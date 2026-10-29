
const root = document.querySelector('[data-notification-popover-root]');

if (root) {
    const trigger = root.querySelector('[data-notification-popover-trigger]');
    const panel = root.querySelector('[data-notification-popover-panel]');
    const list = root.querySelector('[data-notification-popover-items]');
    const loading = root.querySelector('[data-notification-popover-loading]');
    const empty = root.querySelector('[data-notification-popover-empty]');
    const badge = root.querySelector('[data-notification-popover-badge]');
    const unreadCount = root.querySelector('[data-notification-popover-unread-count]');
    const markAllButton = root.querySelector('[data-notification-popover-mark-all]');
    const template = root.querySelector('[data-notification-popover-row-template]');
    const tabs = root.querySelectorAll('[data-notification-popover-tab]');

    const endpoint = '/api/v1/notifications';
    let currentTab = 'all';
    let unreadTotal = Number(unreadCount?.textContent ?? 0);
    let isOpen = false;
    let loadingRequest = false;

    // 未読件数を更新する
    function updateUnreadCount(count) {
        unreadTotal = count;

        // バッジ
        if (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.toggle('hidden', count <= 0);
        }

        // 未読件数
        if (unreadCount) {
            unreadCount.textContent = String(count);
        }


        if (trigger) {
            trigger.setAttribute('aria-label', `通知 (${count} 件未読)`);
        }
    }

    // 通知を既読にする
    function formatDate(value) {
        if (!value) return '';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) return '';

        return new Intl.DateTimeFormat('ja-JP', {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    // タブを切り替える
    function setTab(tab) {
        currentTab = tab;

        tabs.forEach((button) => {
            button.setAttribute(
                'aria-selected',
                String(button.dataset.notificationPopoverTab === tab),
            );
        });
    }

    // 通知パネルを表示/非表示にする
    function showPanel(show) {
        isOpen = show;

        panel.style.display = show ? 'flex' : 'none';
        panel.classList.toggle('hidden', !show);
        panel.classList.toggle('opacity-0', !show);
        panel.classList.toggle('-translate-y-1', !show);

        trigger.setAttribute('aria-expanded', String(show));

        if (show) {
            loadNotifications();
        }
    }

    // 通知をレンダリングする
    function renderNotifications(notifications) {
        list.replaceChildren();

        notifications.forEach((notification) => {
            const fragment = template.content.cloneNode(true);
            const link = fragment.querySelector('[data-notification-popover-row]');
            const title = fragment.querySelector('[data-notification-popover-row-title]');
            const message = fragment.querySelector('[data-notification-popover-row-message]');
            const time = fragment.querySelector('[data-notification-popover-row-time]');
            const dot = fragment.querySelector('[data-notification-popover-row-dot]');

            const isUnread = !notification.read_at;

            title.textContent = notification.title ?? '';
            message.textContent = notification.body ?? '';
            time.textContent = formatDate(notification.created_at);

            link.setAttribute('aria-data-unread', String(isUnread));
            link.dataset.unread = String(isUnread);
            link.href = notification.url || '#';

            dot.classList.toggle('hidden', !isUnread);

            link.addEventListener('click', async (event) => {
                if (!notification.url) {
                    event.preventDefault();
                    return;
                }

                if (!isUnread) return;

                event.preventDefault();

                try {
                    await markAsRead(notification.id);
                    window.location.assign(notification.url);
                } catch (error) {
                    console.error('通知を既読にできませんでした。', error);
                    window.location.assign(notification.url);
                }
            });

            list.appendChild(fragment);
        });

        empty.classList.toggle('hidden', notifications.length > 0);
    }

    // 通知を取得して表示する
    async function loadNotifications() {
        if (loadingRequest) return;

        loadingRequest = true;
        loading.classList.remove('hidden');
        empty.classList.add('hidden');

        try {
            const url = new URL(endpoint, window.location.origin);

            if (currentTab === 'unread') {
                url.searchParams.set('tab', 'unread');
            }

            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`通知取得に失敗しました: ${response.status}`);
            }

            const result = await response.json();

            renderNotifications(result.data ?? []);

            if (typeof result.unread_count === 'number') {
                updateUnreadCount(result.unread_count);
            }
        } catch (error) {
            console.error(error);
            list.replaceChildren();
            empty.textContent = '通知を読み込めませんでした。';
            empty.classList.remove('hidden');
        } finally {
            loading.classList.add('hidden');
            loadingRequest = false;
        }
    }

    // CSRF Cookie を取得する
    async function ensureCsrfCookie() {
        const response = await fetch('/sanctum/csrf-cookie', {
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('CSRF Cookieを取得できませんでした。');
        }
    }

    // XSRF トークンを取得する
    function getXsrfToken() {
        const cookie = document.cookie
            .split('; ')
            .find((item) => item.startsWith('XSRF-TOKEN='));

        return cookie
            ? decodeURIComponent(cookie.substring('XSRF-TOKEN='.length))
            : '';
    }

    // JSON 形式で POST リクエストを送信する
    async function postJson(url) {
        await ensureCsrfCookie();

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
        });

        if (!response.ok) {
            throw new Error(`通知の更新に失敗しました: ${response.status}`);
        }

        return response.json();
    }

    // 通知を既読にする
    async function markAsRead(id) {
        const result = await postJson(
            `${endpoint}/${encodeURIComponent(id)}/read`,
        );

        const readAt = result.data?.read_at;

        if (readAt) {
            updateUnreadCount(Math.max(0, unreadTotal - 1));
        }

        return result;
    }

    trigger.addEventListener('click', () => {
        showPanel(!isOpen);
    });

    tabs.forEach((button) => {
        button.addEventListener('click', () => {
            setTab(button.dataset.notificationPopoverTab);
            loadNotifications();
        });
    });

    // タブ切り替えのイベントリスナー
    markAllButton.addEventListener('click', async () => {
        markAllButton.disabled = true;

        try {
            const result = await postJson(`${endpoint}/read-all`);
            updateUnreadCount(result.unread_count ?? 0);
            await loadNotifications();
        } catch (error) {
            console.error(error);
        } finally {
            markAllButton.disabled = false;
        }
    });
    // 通知パネル外クリックで閉じる
    document.addEventListener('click', (event) => {
        if (isOpen && !root.contains(event.target)) {
            showPanel(false);
        }
    });

}
