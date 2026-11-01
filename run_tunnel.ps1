$cfExe = "c:\New folder\cloudflared.exe"
if (-not (Test-Path $cfExe)) {
    Write-Host "Khong tim thay cloudflared.exe tai $cfExe" -ForegroundColor Red
    pause
    exit
}

Write-Host "===============================================================================" -ForegroundColor Cyan
Write-Host "     DANG KHOI TAO DUONG TRUYEN CLOUDFLARE TUNNEL (LOCALHOST:80)..." -ForegroundColor Yellow
Write-Host "===============================================================================" -ForegroundColor Cyan
Write-Host "Vui long doi trong 3-5 giay..." -ForegroundColor Gray

$processInfo = New-Object System.Diagnostics.ProcessStartInfo
$processInfo.FileName = $cfExe
$processInfo.Arguments = "tunnel --url http://localhost:80"
$processInfo.RedirectStandardError = $true
$processInfo.RedirectStandardOutput = $true
$processInfo.UseShellExecute = $false
$processInfo.CreateNoWindow = $true

$process = [System.Diagnostics.Process]::Start($processInfo)

$found = $false
while (-not $process.HasExited -and -not $found) {
    $line = $process.StandardError.ReadLine()
    if ($line -match "https://[a-zA-Z0-9-]+\.trycloudflare\.com") {
        $baseUrl = $matches[0]
        $fullUrl = "$baseUrl/web01/bai-tap-lon-lap-trinh-web-nhom1/"
        $found = $true

        $txtContent = @"
========================================================================================
🌐 LINK TRUY CẬP WEBSITE HELPDESK TUD TỪ MỌI THIẾT BỊ (INTERNET CÔNG KHAI)
========================================================================================

👉 LINK ĐANG HOẠT ĐỘNG:
$fullUrl

(Bạn có thể sao chép link trên gửi qua Zalo / Facebook / Messenger cho Cô giáo hoặc bạn bè)

----------------------------------------------------------------------------------------
TÀI KHOẢN ĐỂ ĐĂNG NHẬP THỬ NGHIỆM:
- Sinh viên:       224001736   | Mật khẩu: 123456
- Giảng viên:      GV001       | Mật khẩu: 123456
- Kỹ thuật viên:   KTV003      | Mật khẩu: 123456
- Quản trị viên:   ADM001      | Mật khẩu: admin123
========================================================================================
"@
        Set-Content -Path "$PSScriptRoot\LINK_TRUY_CAP_INTERNET.txt" -Value $txtContent -Encoding UTF8

        try { Set-Clipboard -Value $fullUrl } catch {}
        try { Start-Process $fullUrl } catch {}

        Clear-Host
        Write-Host "===============================================================================" -ForegroundColor Green
        Write-Host "        DA KHOI TAO DUONG TRUYEN INTERNET CONG KHAI THANH CONG!" -ForegroundColor Yellow
        Write-Host "===============================================================================" -ForegroundColor Green
        Write-Host ""
        Write-Host " LINK WEBSITE CONG KHAI DANG HOAT DONG:" -ForegroundColor White
        Write-Host "   $fullUrl" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "   [V] Da tu dong sao chep link vao Clipboard (Nhan Ctrl + V de gui luon)!" -ForegroundColor Green
        Write-Host "   [V] Da luu link vao file: LINK_TRUY_CAP_INTERNET.txt" -ForegroundColor Green
        Write-Host "   [V] Da mo trinh duyet voi website cua ban!" -ForegroundColor Green
        Write-Host ""
        Write-Host "===============================================================================" -ForegroundColor Green
        Write-Host " LUU Y: HAY GIU NGUYEN CUA SO NAY CHO DEN KHI DEMO XONG." -ForegroundColor Magenta
        Write-Host "        (Khi dong cua so nay, duong truyen internet se tu dong ngat)." -ForegroundColor Gray
        Write-Host "===============================================================================" -ForegroundColor Green
    }
}

$process.WaitForExit()

