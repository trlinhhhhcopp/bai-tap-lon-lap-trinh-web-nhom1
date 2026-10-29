<?php
namespace App\Services;

use Exception;

class UploadService {
    private static string $uploadDir = __DIR__ . '/../../storage/uploads/';
    private static array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    private static array $allowedMimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'
    ];
    private static int $maxSizeBytes = 5 * 1024 * 1024; // 5MB

    public static function getUploadDir(): string {
        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0777, true);
        }
        return self::$uploadDir;
    }

    /**
     * Xử lý file tải lên từ thiết bị (input type="file")
     */
    public static function handleUploadedFile(?array $file): ?string {
        if (!$file || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > self::$maxSizeBytes) {
            throw new Exception("Dung lượng file vượt quá giới hạn cho phép (Tối đa 5MB)!");
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::$allowedExtensions, true)) {
            throw new Exception("Định dạng file không hợp lệ! Chỉ chấp nhận ảnh (JPG, PNG, GIF, WEBP) hoặc tài liệu PDF.");
        }

        // Kiểm tra MIME Type thực tế
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::$allowedMimes, true)) {
            throw new Exception("Kiểm duyệt bảo mật thất bại: Tệp tải lên không phải ảnh hoặc PDF an toàn!");
        }

        // Đối với ảnh, xác minh bằng getimagesize
        if (str_starts_with($mime, 'image/')) {
            $imgInfo = @getimagesize($file['tmp_name']);
            if ($imgInfo === false) {
                throw new Exception("Tệp ảnh bị lỗi hoặc không hợp lệ!");
            }
        }

        $uploadDir = self::getUploadDir();
        $safeName = 'evidence_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new Exception("Không thể lưu trữ tệp đính kèm!");
        }

        return $safeName;
    }

    /**
     * Xử lý ảnh chụp trực tiếp từ Camera / Webcam (Base64 data URL)
     */
    public static function handleBase64Snapshot(?string $dataUrl): ?string {
        if (empty($dataUrl) || !str_starts_with($dataUrl, 'data:image/')) {
            return null;
        }

        if (!preg_match('#^data:image/(png|jpeg|jpg|webp);base64,#i', $dataUrl, $matches)) {
            throw new Exception("Dữ liệu ảnh chụp trực tiếp không hợp lệ!");
        }

        $ext = strtolower($matches[1]);
        if ($ext === 'jpeg') $ext = 'jpg';

        $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $decoded = base64_decode($base64Data);

        if ($decoded === false) {
            throw new Exception("Không thể giải mã dữ liệu ảnh chụp từ máy ảnh!");
        }

        if (strlen($decoded) > self::$maxSizeBytes) {
            throw new Exception("Ảnh chụp vượt quá dung lượng cho phép (Tối đa 5MB)!");
        }

        $uploadDir = self::getUploadDir();
        $safeName = 'snapshot_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $safeName;

        if (file_put_contents($destPath, $decoded) === false) {
            throw new Exception("Không thể lưu ảnh chụp máy ảnh!");
        }

        return $safeName;
    }
}

