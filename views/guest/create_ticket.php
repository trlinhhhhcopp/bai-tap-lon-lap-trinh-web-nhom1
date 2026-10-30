<?php 
require_once __DIR__ . '/../../config/database.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    (new \App\Controllers\GuestTicketController())->submit();
    exit;
}
require __DIR__ . '/../layouts/header.php'; 
?>

<div class="my-auto py-8 flex items-center justify-center">
    <div class="w-full max-w-2xl bg-white border border-slate-200/80 rounded-3xl shadow-2xl shadow-indigo-950/10 overflow-hidden transition-all">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-rose-950 to-slate-900 text-white p-6 md:p-8 text-center border-b-2 border-rose-500 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-32 h-32 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                    🆘 CỨU HỘ KHẨN CẤP
                </span>
                <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    CỨU HỘ TÀI KHOẢN KHẨN CẤP (GUEST TICKET)
                </h2>
                <p class="text-xs text-slate-300">
                    Dành cho sinh viên & cán bộ bị khóa mềm (quá 5 lần), khóa cứng hoặc mất quyền truy cập
                </p>
            </div>
        </div>

        <!-- Body Form -->
        <div class="p-6 md:p-8 space-y-5">
            <div class="bg-rose-50/80 border border-rose-200 text-rose-900 p-4 rounded-2xl text-xs leading-relaxed flex items-start gap-3">
                <span class="text-xl shrink-0">⚠️</span>
                <div>
                    <strong>Quy trình cứu hộ khẩn cấp:</strong> Khi bạn không thể đăng nhập do <strong>quên mật khẩu</strong>, <strong>bị khóa mềm 30 phút</strong> hoặc <strong>bị khóa tài khoản</strong>, hãy gửi đơn cứu hộ này. Đơn sẽ gửi thẳng đến <strong>Kỹ thuật viên chuyên trách SSO (KTV Nguyễn Văn An)</strong> để xử lý mở khóa ngay. Sau khi gửi, bạn sẽ nhận được <strong>Mã tra cứu duy nhất</strong> để theo dõi tiến độ mà không cần đăng nhập!
                </div>
            </div>

            <form method="POST" action="<?= $appBase ?>guest/ticket" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Mã Sinh Viên / Mã cán bộ (*):
                        </label>
                        <input type="text" name="user_code" required 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="VD: 224001729">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Email cá nhân nhận kết quả (Gmail...) (*):
                        </label>
                        <input type="email" name="contact_email" required 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="VD: linh.personal@gmail.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Chọn sự cố cần cứu hộ (*):
                    </label>
                    <select id="guestIssueSelect" onchange="onGuestIssueChange()" 
                            class="w-full px-4 py-3 bg-rose-50/60 border border-rose-300 rounded-xl text-xs font-bold text-rose-900 focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition">
                        <option value="">-- Bấm để chọn sự cố... --</option>
                        <option value="lock_soft">Tài khoản bị khóa mềm 30 phút do gõ sai mật khẩu quá 5 lần</option>
                        <option value="forgot_pass">Quên mật khẩu / Không nhớ mật khẩu tài khoản trường cấp</option>
                        <option value="lock_hard">Tài khoản bị khóa cứng (kỷ luật / hết hạn khóa học)</option>
                        <option value="sso_sync">Lỗi xác thực định danh / Không thể đăng nhập vào Portal và Canvas</option>
                        <option value="other">✏️ Sự cố khác (Tự nhập lý do bên dưới)...</option>
                    </select>
                    <input type="hidden" name="issue_title" id="guestIssueTitleInput" value="Yêu cầu cứu hộ mở khóa tài khoản">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mô tả hiện tượng lỗi / Lý do bị khóa chi tiết (*):
                    </label>
                    <textarea name="description" id="guestDescriptionInput" rows="3" required 
                              class="w-full p-4 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed placeholder:text-slate-400"
                              placeholder="Chọn sự cố ở trên để hệ thống tự động điền mẫu, hoặc tự nhập mô tả cụ thể tại đây..."></textarea>
                </div>

                <!-- Đính kèm ảnh thẻ sinh viên hoặc ảnh chụp màn hình lỗi -->
                <div class="p-4 bg-white border border-dashed border-slate-300 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">
                            Đính kèm minh chứng xác thực (Ảnh thẻ SV / Ảnh chụp lỗi màn hình):
                        </label>
                        <span class="text-[11px] text-slate-400">PNG, JPG, PDF</span>
                    </div>
                    <p class="text-[11.5px] text-slate-500">
                        KTV cần ảnh thẻ SV hoặc CCCD để đối chiếu mở khóa. Bạn có thể chọn tải file từ thiết bị hoặc chụp trực tiếp bằng Camera:
                    </p>

                    <div class="flex gap-2 flex-wrap">
                        <label class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold cursor-pointer transition">
                            <span>📁</span> Tải tệp từ thiết bị
                            <input type="file" name="attachment" id="guestFileInput" accept="image/*,.pdf" class="hidden" onchange="onGuestFileSelected(this)">
                        </label>
                        <button type="button" onclick="startGuestCamera()" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition">
                            <span>📸</span> Chụp ảnh từ Camera
                        </button>
                    </div>

                    <!-- Khung camera live stream -->
                    <div id="guestCameraBox" style="display: none;" class="mt-3 p-3 bg-slate-900 rounded-xl text-center space-y-2.5">
                        <video id="guestWebcamVideo" autoplay playsinline class="w-full max-w-md mx-auto max-h-56 rounded-lg bg-black"></video>
                        <div class="flex justify-center gap-2">
                            <button type="button" onclick="captureGuestSnapshot()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition">
                                📸 Bấm Chụp Ngay
                            </button>
                            <button type="button" onclick="stopGuestCamera()" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl text-xs transition">
                                ✕ Đóng Camera
                            </button>
                        </div>
                        <canvas id="guestSnapshotCanvas" class="hidden"></canvas>
                    </div>

                    <!-- Khung xem trước ảnh preview -->
                    <div id="guestPreviewBox" style="display: none;" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-700" id="guestPreviewStatusText">✓ Đã đính kèm ảnh minh chứng:</span>
                            <button type="button" onclick="clearGuestAttachment()" class="text-rose-600 hover:text-rose-700 font-bold hover:underline">
                                🗑️ Xóa / Chọn lại
                            </button>
                        </div>
                        <div class="text-center py-1">
                            <img id="guestPreviewImage" src="" alt="Ảnh minh chứng xác thực" class="max-h-48 mx-auto rounded-lg shadow-sm border border-slate-200">
                        </div>
                    </div>

                    <input type="hidden" name="captured_image" id="guestCapturedImageInput" value="">
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-rose-600 via-rose-700 to-red-800 hover:from-rose-700 hover:to-red-900 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-rose-600/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Gửi Yêu Cầu Cứu Hộ Ngay</span>
                    <span>➔</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-center text-xs text-slate-500">
                Đã có mã phiếu cứu hộ? <a href="<?= $appBase ?>guest/lookup" class="text-sky-600 hover:text-sky-700 font-bold hover:underline">Bấm vào đây để tra cứu tiến độ ngay</a>
            </div>
        </div>
    </div>
</div>

<script>
const guestTemplates = {
    lock_soft: {
        title: "Tài khoản bị khóa mềm 30 phút do gõ sai pass quá 5 lần",
        desc: "Em gõ nhầm mật khẩu cũ quá 5 lần liên tiếp khiến tài khoản bị khóa mềm 30 phút. Em cần vào nộp bài gấp, kính nhờ anh chị KTV SSO mở khóa tài khoản sớm giúp em."
    },
    forgot_pass: {
        title: "Quên mật khẩu / Yêu cầu cấp lại mật khẩu",
        desc: "Em bị quên mật khẩu tài khoản trường cấp và không thể đăng nhập. Kính nhờ KTV kiểm tra thông tin sinh viên và cấp lại mật khẩu mới gửi về email cá nhân giúp em."
    },
    lock_hard: {
        title: "Yêu cầu mở khóa tài khoản bị khóa cứng (kèm xác minh)",
        desc: "Tài khoản của em hiện báo bị khóa cứng. Em gửi kèm thông tin liên hệ và ảnh minh chứng để KTV kiểm tra hồ sơ đào tạo và hỗ trợ mở khóa giúp em."
    },
    sso_sync: {
        title: "Lỗi xác thực định danh / Không thể đăng nhập vào Portal và Canvas",
        desc: "Tài khoản của em đã nhập đúng mật khẩu nhưng hệ thống báo lỗi xác thực máy chủ SSO và không điều hướng vào được trang học tập Canvas."
    }
};

function onGuestIssueChange() {
    const val = document.getElementById('guestIssueSelect').value;
    const titleInput = document.getElementById('guestIssueTitleInput');
    const descInput = document.getElementById('guestDescriptionInput');

    if (val === '') return;

    if (val === 'other') {
        titleInput.value = 'Yêu cầu hỗ trợ mở khóa tài khoản';
        descInput.value = '';
        descInput.focus();
        return;
    }

    const tpl = guestTemplates[val];
    if (tpl) {
        titleInput.value = tpl.title;
        descInput.value = tpl.desc;
    }
}

let guestStream = null;

function startGuestCamera() {
    const box = document.getElementById('guestCameraBox');
    const video = document.getElementById('guestWebcamVideo');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Thiết bị không hỗ trợ camera trực tiếp!");
        return;
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(stream => {
            guestStream = stream;
            video.srcObject = stream;
            box.style.display = 'block';
        })
        .catch(err => {
            alert("Không thể bật Camera. Vui lòng cấp quyền truy cập Camera!");
        });
}

function stopGuestCamera() {
    if (guestStream) {
        guestStream.getTracks().forEach(t => t.stop());
        guestStream = null;
    }
    const box = document.getElementById('guestCameraBox');
    if (box) box.style.display = 'none';
}

function captureGuestSnapshot() {
    const video = document.getElementById('guestWebcamVideo');
    const canvas = document.getElementById('guestSnapshotCanvas');
    if (!video || !video.videoWidth) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

    document.getElementById('guestCapturedImageInput').value = dataUrl;
    const fileInput = document.getElementById('guestFileInput');
    if (fileInput) fileInput.value = '';

    document.getElementById('guestPreviewImage').src = dataUrl;
    document.getElementById('guestPreviewStatusText').textContent = '📸 Đã chụp ảnh thành công từ Camera:';
    document.getElementById('guestPreviewBox').style.display = 'block';

    stopGuestCamera();
}

function onGuestFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('guestCapturedImageInput').value = '';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('guestPreviewImage').src = e.target.result;
                document.getElementById('guestPreviewStatusText').textContent = `📁 Đã chọn file ảnh: ${file.name} (${Math.round(file.size / 1024)} KB)`;
                document.getElementById('guestPreviewBox').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('guestPreviewImage').src = '';
            document.getElementById('guestPreviewStatusText').textContent = `📄 Đã chọn tệp: ${file.name} (${Math.round(file.size / 1024)} KB)`;
            document.getElementById('guestPreviewBox').style.display = 'block';
        }
    }
}

function clearGuestAttachment() {
    const fileInput = document.getElementById('guestFileInput');
    if (fileInput) fileInput.value = '';
    const capInput = document.getElementById('guestCapturedImageInput');
    if (capInput) capInput.value = '';
    const previewBox = document.getElementById('guestPreviewBox');
    if (previewBox) previewBox.style.display = 'none';
    const previewImg = document.getElementById('guestPreviewImage');
    if (previewImg) previewImg.src = '';
    stopGuestCamera();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
