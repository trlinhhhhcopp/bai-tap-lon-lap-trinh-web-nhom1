<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 650px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); color: #fff; padding: 22px; text-align: center;">
        <h2 style="font-size: 19px; margin-bottom: 4px;">🆘 Cứu Hộ Tài Khoản Khẩn Cấp (Guest Ticket)</h2>
        <p style="font-size: 13px; opacity: 0.9;">Dành cho sinh viên & cán bộ bị khóa mềm (nhập sai pass quá 5 lần), khóa cứng hoặc không thể đăng nhập</p>
    </div>

    <div style="padding: 26px;">
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; line-height: 1.5;">
            ⚠️ <strong>Quy trình cứu hộ khẩn cấp:</strong> Khi bạn không thể đăng nhập do <strong>quên mật khẩu</strong>, <strong>bị khóa mềm 30 phút</strong> hoặc <strong>bị khóa tài khoản</strong>, hãy gửi đơn cứu hộ này. Đơn sẽ gửi thẳng đến <strong>Kỹ thuật viên chuyên trách SSO (KTV Nguyễn Văn An)</strong> để xử lý mở khóa ngay. Sau khi gửi, bạn sẽ nhận được <strong>Mã tra cứu duy nhất</strong> để theo dõi tiến độ mà không cần đăng nhập!
        </div>

        <form method="POST" action="<?= $appBase ?>public/index.php/guest/ticket" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Mã Sinh Viên (*):</label>
                    <input type="text" name="user_code" required class="form-control" placeholder="VD: 224001729" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Họ và tên (*):</label>
                    <input type="text" name="name" required class="form-control" placeholder="VD: Trần Thục Linh" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Email cá nhân nhận kết quả (Gmail...):</label>
                    <input type="email" name="contact_email" required class="form-control" placeholder="VD: linh.personal@gmail.com" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Số điện thoại liên hệ:</label>
                    <input type="text" name="phone" required class="form-control" placeholder="VD: 0901234567" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
            </div>

            <!-- Mục chọn sự cố cứu hộ chuẩn giống như trong phiếu báo sự cố -->
            <div style="margin-bottom: 14px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Chọn sự cố cần cứu hộ (*):</label>
                <select id="guestIssueSelect" onchange="onGuestIssueChange()" style="width:100%; padding:9px 10px; border:1px solid #ef4444; border-radius:6px; background:#fef2f2; color:#991b1b; font-size:13px; font-weight:600;">
                    <option value="">-- Bấm để chọn sự cố... --</option>
                    <option value="lock_soft">Tài khoản bị khóa mềm 30 phút do gõ sai mật khẩu quá 5 lần</option>
                    <option value="forgot_pass">Quên mật khẩu / Không nhớ mật khẩu tài khoản trường cấp</option>
                    <option value="lock_hard">Tài khoản bị khóa cứng (kỷ luật / hết hạn khóa học)</option>
                    <option value="sso_sync">Lỗi xác thực định danh / Không thể đăng nhập vào Portal và Canvas</option>
                    <option value="other">✏️ Sự cố khác (Tự nhập lý do bên dưới)...</option>
                </select>
                <input type="hidden" name="issue_title" id="guestIssueTitleInput" value="Yêu cầu cứu hộ mở khóa tài khoản">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Mô tả hiện tượng lỗi / Lý do bị khóa chi tiết:</label>
                <textarea name="description" id="guestDescriptionInput" rows="4" required class="form-control" placeholder="Chọn sự cố ở trên để hệ thống tự động điền mẫu, hoặc tự nhập mô tả cụ thể tại đây..." style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; resize:vertical; font-size:13px; line-height:1.5;"></textarea>
            </div>

            <!-- Đính kèm ảnh thẻ sinh viên hoặc ảnh chụp màn hình lỗi -->
            <div style="margin-bottom: 20px; padding: 14px; background: #fafafa; border: 1px dashed #cbd5e1; border-radius: 8px;">
                <label style="display:block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    Đính kèm minh chứng xác thực (Ảnh thẻ SV / Ảnh chụp lỗi màn hình):
                </label>
                <div style="font-size: 12px; color: #64748b; margin-bottom: 10px;">
                    KTV cần ảnh thẻ SV hoặc CCCD để đối chiếu mở khóa. Bạn có thể chọn tải file từ thiết bị hoặc chụp trực tiếp bằng Camera:
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                        📁 Tải tệp từ thiết bị
                        <input type="file" name="attachment" id="guestFileInput" accept="image/*,.pdf" style="display: none;" onchange="onGuestFileSelected(this)">
                    </label>

                    <button type="button" onclick="startGuestCamera()" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                        📸 Chụp ảnh từ Camera
                    </button>
                </div>

                <!-- Khung camera live stream -->
                <div id="guestCameraBox" style="display: none; margin-top: 12px; padding: 12px; background: #0f172a; border-radius: 8px; text-align: center;">
                    <video id="guestWebcamVideo" autoplay playsinline style="width: 100%; max-width: 480px; max-height: 280px; border-radius: 6px; background: #000;"></video>
                    <div style="margin-top: 10px; display: flex; justify-content: center; gap: 10px;">
                        <button type="button" onclick="captureGuestSnapshot()" style="padding: 7px 18px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">
                            📸 Bấm Chụp Ngay
                        </button>
                        <button type="button" onclick="stopGuestCamera()" style="padding: 7px 14px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                            ✕ Đóng Camera
                        </button>
                    </div>
                    <canvas id="guestSnapshotCanvas" style="display: none;"></canvas>
                </div>

                <!-- Khung xem trước ảnh preview -->
                <div id="guestPreviewBox" style="display: none; margin-top: 12px; padding: 12px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #15803d;" id="guestPreviewStatusText">✓ Đã đính kèm ảnh minh chứng:</span>
                        <button type="button" onclick="clearGuestAttachment()" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; font-weight: 600; cursor: pointer;">
                            🗑️ Xóa / Chọn lại
                        </button>
                    </div>
                    <div style="text-align: center;">
                        <img id="guestPreviewImage" src="" alt="Ảnh minh chứng xác thực" style="max-width: 100%; max-height: 200px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                </div>

                <input type="hidden" name="captured_image" id="guestCapturedImageInput" value="">
            </div>

            <button type="submit" style="width:100%; padding:12px; background: #dc2626; color:#fff; font-size:14.5px; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                Gửi Yêu Cầu Cứu Hộ Ngay ➔
            </button>

            <div style="text-align: center; margin-top: 18px; font-size: 13px;">
                Đã có mã phiếu cứu hộ? <a href="<?= $appBase ?>public/index.php/guest/lookup" style="color: var(--primary); text-decoration: none; font-weight: 600;">Bấm vào đây để tra cứu tiến độ ngay</a>
            </div>
        </form>
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
        title: "Lỗi xác thực định danh / Không đăng nhập được Portal và Canvas",
        desc: "Tài khoản của em đã nhập đúng mật khẩu nhưng hệ thống báo lỗi xác thực máy chủ SSO và không điều hướng vào được trang học tập Canvas."
    }
};

function onGuestIssueChange() {
    const val = document.getElementById('guestIssueSelect').value;
    const titleInput = document.getElementById('guestIssueTitleInput');
    const descInput = document.getElementById('guestDescriptionInput');

    if (!val || val === 'other') {
        titleInput.value = 'Yêu cầu cứu hộ mở khóa tài khoản';
        if (val === 'other') {
            descInput.value = '';
            descInput.placeholder = 'Mô tả cụ thể sự cố bạn đang gặp phải...';
            descInput.focus();
        }
        return;
    }

    const tpl = guestTemplates[val];
    if (tpl) {
        titleInput.value = tpl.title;
        descInput.value = tpl.desc;
    }
}

// Xử lý camera & tệp đính kèm cho Guest Ticket
let guestMediaStream = null;

function startGuestCamera() {
    const cameraBox = document.getElementById('guestCameraBox');
    const video = document.getElementById('guestWebcamVideo');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Trình duyệt không hỗ trợ camera trực tiếp. Vui lòng chọn 'Tải tệp từ thiết bị'!");
        return;
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(stream => {
            guestMediaStream = stream;
            video.srcObject = stream;
            cameraBox.style.display = 'block';
        })
        .catch(err => {
            alert("Không thể truy cập Camera. Vui lòng cấp quyền hoặc dùng nút 'Tải tệp từ thiết bị'!");
        });
}

function stopGuestCamera() {
    if (guestMediaStream) {
        guestMediaStream.getTracks().forEach(track => track.stop());
        guestMediaStream = null;
    }
    const cameraBox = document.getElementById('guestCameraBox');
    if (cameraBox) cameraBox.style.display = 'none';
}

function captureGuestSnapshot() {
    const video = document.getElementById('guestWebcamVideo');
    const canvas = document.getElementById('guestSnapshotCanvas');
    if (!video || !video.videoWidth) {
        alert("Camera chưa sẵn sàng, vui lòng đợi 1 giây!");
        return;
    }

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
