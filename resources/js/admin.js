import './echo';

document.addEventListener('DOMContentLoaded', () => {
    const userId = document.body.dataset.userId;
    if (!userId || !window.Echo) {
        return;
    }

    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            const badge = document.getElementById('notification-count');
            if (badge) {
                badge.textContent = String(parseInt(badge.textContent || '0', 10) + 1);
                badge.style.display = '';
            }

            const list = document.getElementById('notification-list');
            if (!list) {
                return;
            }

            const emptyItem = list.querySelector('.no-notifications');
            if (emptyItem) {
                emptyItem.remove();
            }

            const li = document.createElement('li');
            li.innerHTML = `<a href="/admin/notifications/${notification.id}" class="message-item item-4">
                <div class="image"><i class="icon-noti-4"></i></div>
                <div>
                    <div class="body-title-2">New order: ${notification.order_number}</div>
                    <div class="text-tiny">${notification.customer_name} &mdash; RM ${Number(notification.total).toFixed(2)}</div>
                </div>
            </a>`;
            list.insertBefore(li, list.children[1]);
        });
});
