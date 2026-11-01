<?php
/**
 * ==============================================================================
 * BANNER THÔNG BÁO THỜI GIAN THỰC (ĐẶT Ở ĐẦU TRANG SAU PHẦN TỔNG QUAN)
 * Phục vụ tất cả các role: Sinh viên, Giảng viên, KTV, Quản trị viên
 * ==============================================================================
 */

require_once __DIR__ . '/../app/Services/NotificationService.php';

if (empty($currentUser) && !empty($_SESSION['user'])) {
    $currentUser = $_SESSION['user'];
}

if (!empty($currentUser)) {
    // Đảm bảo có dữ liệu thông báo sinh động phù hợp vai trò
    NotificationService::ensureUserHasInitialNotifications($currentUser);

    $bannerNotif = NotificationService::getLatestBanner((int)$currentUser['id']);
} else {
    $bannerNotif = null;
}
?>

<?php if (!empty($bannerNotif)): ?>
    <?php
    $typeIcons = [
        'ticket_reply'      => '💬',
        'new_assignment'    => '📝',
        'class_assigned'    => '🏫',
        'ticket_assigned'   => '🎫',
        'deadline_extended' => '⏳',
        'guest_rescue'      => '🆘',
        'system'            => '🔔',
    ];
    $icon = $typeIcons[$bannerNotif['type']] ?? '🔔';
    $isUnread = ((int)$bannerNotif['is_read'] === 0);
    $notifLink = !empty($bannerNotif['link']) ? $appBase . ltrim($bannerNotif['link'], '/') : null;
    ?>
    <div id="app_notification_banner" class="relative overflow-hidden bg-gradient-to-r from-indigo-50 via-sky-50 to-indigo-50/80 border-2 <?php echo $isUnread ? 'border-indigo-300' : 'border-slate-200'; ?> rounded-3xl p-4 sm:p-5 shadow-xs transition-all duration-300">
        <div class="flex items-start sm:items-center justify-between gap-4 flex-col sm:flex-row">
            <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                <div class="relative w-11 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0">
                    <span><?php echo $icon; ?></span>
                    <?php if ($isUnread): ?>
                        <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-rose-500 border-2 border-white"></span>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase <?php echo $isUnread ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700'; ?>">
                            <?php echo $isUnread ? '🔔 THÔNG BÁO MỚI' : 'ĐÃ ĐỌC'; ?>
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono">
                            <?php echo date('H:i - d/m/Y', strtotime($bannerNotif['created_at'])); ?>
                        </span>
                    </div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 leading-snug">
                        <?php echo htmlspecialchars($bannerNotif['title']); ?>
                    </h3>
                    <p class="text-[11.5px] text-slate-600 mt-0.5 leading-relaxed line-clamp-2">
                        <?php echo htmlspecialchars($bannerNotif['content']); ?>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center w-full sm:w-auto justify-end">
                <?php if ($notifLink): ?>
                    <a href="<?php echo htmlspecialchars($notifLink); ?>" 
                       onclick="markNotificationReadAndGo(<?php echo (int)$bannerNotif['id']; ?>)"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <span>Xem Chi Tiết</span>
                        <span>→</span>
                    </a>
                <?php endif; ?>
                <button type="button" 
                        onclick="dismissBanner(<?php echo (int)$bannerNotif['id']; ?>)" 
                        title="Đã xem / Ẩn thông báo này"
                        class="px-3 py-2 bg-white hover:bg-slate-100 text-slate-600 font-bold rounded-xl text-xs transition border border-slate-200 flex items-center gap-1 cursor-pointer shadow-2xs">
                    <span>✕</span> <span class="hidden sm:inline">Ẩn</span>
                </button>
            </div>
        </div>
    </div>

    <script>
    function dismissBanner(notifId) {
        const banner = document.getElementById('app_notification_banner');
        if (banner) {
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-10px)';
            setTimeout(() => banner.remove(), 300);
        }
        // Gửi request ngầm đánh dấu đã đọc
        fetch('<?php echo $appBase; ?>index.php?action=api_mark_notification_read&id=' + notifId, { method: 'POST' })
            .catch(err => console.log(err));
    }

    function markNotificationReadAndGo(notifId) {
        fetch('<?php echo $appBase; ?>index.php?action=api_mark_notification_read&id=' + notifId, { method: 'POST' })
            .catch(err => console.log(err));
    }
    </script>
<?php endif; ?>
