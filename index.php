<?php
require_once __DIR__ . '/auth.php';
rktv_require_admin(false);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK TV — Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #0a0a0f;
            --bg2: #111118;
            --bg3: #18181f;
            --bg4: #1e1e28;
            --accent: #e8003d;
            --accent2: #ff2257;
            --green: #1fb35a;
            --text: #ffffff;
            --text2: #a0a0b8;
            --border: rgba(255, 255, 255, 0.06);
        }

        html,
        body {
            background: var(--bg);
            color: var(--text);
            font-family: Arial, sans-serif;
            min-height: 100%;
        }

        .topbar {
            height: 60px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h1 {
            font-size: 18px;
            font-weight: 900;
        }

        .topbar h1 span {
            color: var(--accent2);
        }

        .logout-btn {
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text2);
            font-size: 13px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            transition: background .2s, color .2s;
        }

        .logout-btn:hover {
            background: var(--bg4);
            color: #fff;
        }

        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px 20px 60px;
        }

        /* ── FORM CARD ── */
        .form-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 24px;
        }

        .form-card h2 {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 700px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text2);
            margin-bottom: 6px;
        }

        .field input[type="text"],
        .field input[type="number"],
        .field select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg3);
            color: #fff;
            font-size: 13px;
            outline: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: rgba(255, 255, 255, .2);
        }

        .checkbox-row {
            display: flex;
            gap: 20px;
            margin-bottom: 16px;
        }

        .checkbox-row label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text2);
            cursor: pointer;
        }

        .checkbox-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .btn-row {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 22px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
        }

        .btn:hover {
            opacity: .88;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-secondary {
            background: var(--bg3);
            color: var(--text2);
            border: 1px solid var(--border);
        }

        /* ── TOOLBAR ── */
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar .left {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text2);
            font-size: 12px;
            font-weight: 700;
            padding: 7px 14px;
            border-radius: 20px;
            cursor: pointer;
            transition: all .2s;
        }

        .filter-pill.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .filter-pill:hover:not(.active) {
            background: var(--bg4);
            color: #fff;
        }

        #searchAdmin {
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--bg3);
            color: #fff;
            font-size: 13px;
            outline: none;
            min-width: 200px;
        }

        /* ── TABLE ── */
        .table-wrap {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            color: var(--text2);
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--bg3);
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .ch-name {
            font-weight: 700;
        }

        .ch-url {
            color: var(--text2);
            font-size: 11px;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
            text-transform: capitalize;
        }

        .badge-sports {
            background: rgba(34, 158, 217, .15);
            color: #5cc4f0;
        }

        .badge-news {
            background: rgba(232, 0, 61, .15);
            color: #ff6b8a;
        }

        .badge-entertainment {
            background: rgba(168, 85, 247, .15);
            color: #c89bfa;
        }

        .badge-movies {
            background: rgba(245, 158, 11, .15);
            color: #fbbf66;
        }

        .badge-kids {
            background: rgba(34, 197, 94, .15);
            color: #6ee0a0;
        }

        .badge-others {
            background: rgba(160, 160, 184, .15);
            color: var(--text2);
        }

        .star-toggle,
        .active-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 17px;
            line-height: 1;
            padding: 4px;
            transition: transform .15s;
        }

        .star-toggle:hover,
        .active-toggle:hover {
            transform: scale(1.2);
        }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-dot.on {
            background: var(--green);
        }

        .status-dot.off {
            background: #555;
        }

        .row-actions {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            background: var(--bg3);
            border: 1px solid var(--border);
            color: var(--text2);
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s, color .2s;
        }

        .icon-btn:hover {
            background: var(--bg4);
            color: #fff;
        }

        .icon-btn.danger:hover {
            background: rgba(232, 0, 61, .15);
            color: #ff6b8a;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text2);
            font-size: 13px;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--bg4);
            border: 1px solid var(--border);
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity .25s, transform .25s;
            z-index: 200;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.error {
            border-color: var(--accent);
            color: #ff6b8a;
        }

        .count-pill {
            color: var(--text2);
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="topbar">
        <h1>Admin Panel</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="wrap">

        <!-- ADD / EDIT FORM -->
        <div class="form-card">
            <h2 id="formTitle">Add Channel</h2>
            <form id="channelForm">
                <input type="hidden" id="channelId" value="">

                <div class="grid-2">
                    <div class="field">
                        <label for="name">Channel Name *</label>
                        <input type="text" id="name" placeholder="e.g. BTV" required>
                    </div>
                    <div class="field">
                        <label for="category">Category *</label>
                        <select id="category">
                            <option value="sports">Sports</option>
                            <option value="news">News</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="movies">Movies</option>
                            <option value="kids">Kids</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="url">Stream URL (.m3u8) *</label>
                    <input type="text" id="url" placeholder="https://example.com/stream.m3u8" required>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label for="logo">Logo Path (optional)</label>
                        <input type="text" id="logo" placeholder="img/BTV.png">
                    </div>
                    <div class="field">
                        <label for="sortOrder">Sort Order</label>
                        <input type="number" id="sortOrder" value="0">
                    </div>
                </div>

                <div class="checkbox-row">
                    <label><input type="checkbox" id="isPopular"> ⭐ Popular</label>
                    <label><input type="checkbox" id="isActive" checked> Active (visible to users)</label>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Channel</button>
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn"
                        style="display:none">Cancel</button>
                </div>
            </form>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="left">
                <button class="filter-pill active" data-cat="popular">⭐ Popular</button>
                <button class="filter-pill" data-cat="sports">Sports</button>
                <button class="filter-pill" data-cat="news">News</button>
                <button class="filter-pill" data-cat="entertainment">Entertainment</button>
                <button class="filter-pill" data-cat="movies">Movies</button>
                <button class="filter-pill" data-cat="kids">Kids</button>
                <button class="filter-pill" data-cat="others">Others</button>
            </div>
            <input type="text" id="searchAdmin" placeholder="Search channels...">
        </div>

        <div class="count-pill" id="countPill" style="margin-bottom:10px;"></div>

        <!-- TABLE -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Channel</th>
                        <th>Category</th>
                        <th>Popular</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="channelTableBody">
                    <!-- rows injected by JS -->
                </tbody>
            </table>
            <div class="empty-state" id="emptyState" style="display:none">কোনো চ্যানেল পাওয়া যায়নি।</div>
        </div>

    </div>

    <div class="toast" id="toast"></div>

    <script>
        let allChannels = [];
        let currentCatFilter = 'popular';

        const form = document.getElementById('channelForm');
        const tableBody = document.getElementById('channelTableBody');
        const emptyState = document.getElementById('emptyState');
        const countPill = document.getElementById('countPill');
        const toast = document.getElementById('toast');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const submitBtn = document.getElementById('submitBtn');
        const formTitle = document.getElementById('formTitle');

        function showToast(msg, isError) {
            toast.textContent = msg;
            toast.className = 'toast show' + (isError ? ' error' : '');
            setTimeout(() => { toast.className = 'toast'; }, 2500);
        }

        async function loadChannels() {
            try {
                const res = await fetch('api.php');
                const data = await res.json();
                if (!data.ok) { showToast('লোড করতে সমস্যা হয়েছে', true); return; }
                allChannels = data.channels;
                renderTable();
            } catch (e) {
                showToast('সার্ভারের সাথে সংযোগ করা যায়নি', true);
            }
        }

        function renderTable() {
            const q = document.getElementById('searchAdmin').value.toLowerCase();

            const filtered = allChannels.filter(ch => {
                const matchCat = currentCatFilter === 'popular' ? ch.is_popular : ch.category === currentCatFilter;
                const matchSearch = ch.name.toLowerCase().includes(q);
                return matchCat && matchSearch;
            });

            countPill.textContent = filtered.length + ' channel(s)';

            if (filtered.length === 0) {
                tableBody.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }
            emptyState.style.display = 'none';

            tableBody.innerHTML = filtered.map(ch => `
        <tr data-id="${ch.id}">
            <td>
                <div class="ch-name">${escapeHtml(ch.name)}</div>
                <span class="ch-url" title="${escapeHtml(ch.url)}">${escapeHtml(ch.url)}</span>
            </td>
            <td><span class="badge badge-${ch.category}">${ch.category}</span></td>
            <td>
                <button class="star-toggle" data-action="toggle_popular" data-id="${ch.id}" title="Toggle popular">
                    ${ch.is_popular ? '⭐' : '☆'}
                </button>
            </td>
            <td>
                <button class="active-toggle" data-action="toggle_active" data-id="${ch.id}" title="Toggle active">
                    <span class="status-dot ${ch.is_active ? 'on' : 'off'}"></span>${ch.is_active ? 'Active' : 'Hidden'}
                </button>
            </td>
            <td>${ch.sort_order}</td>
            <td>
                <div class="row-actions">
                    <button class="icon-btn" data-action="edit" data-id="${ch.id}">Edit</button>
                    <button class="icon-btn danger" data-action="delete" data-id="${ch.id}">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // ── FILTER PILLS ──
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                currentCatFilter = pill.dataset.cat;
                renderTable();
            });
        });

        document.getElementById('searchAdmin').addEventListener('input', renderTable);

        // ── FORM SUBMIT (CREATE / UPDATE) ──
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('channelId').value;
            const fd = new FormData();
            fd.append('action', id ? 'update' : 'create');
            if (id) fd.append('id', id);
            fd.append('name', document.getElementById('name').value.trim());
            fd.append('url', document.getElementById('url').value.trim());
            fd.append('logo', document.getElementById('logo').value.trim());
            fd.append('category', document.getElementById('category').value);
            fd.append('sort_order', document.getElementById('sortOrder').value || '0');
            fd.append('is_popular', document.getElementById('isPopular').checked ? '1' : '0');
            fd.append('is_active', document.getElementById('isActive').checked ? '1' : '0');

            try {
                const res = await fetch('api.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    showToast(id ? 'চ্যানেল আপডেট হয়েছে' : 'চ্যানেল যুক্ত হয়েছে');
                    resetForm();
                    loadChannels();
                } else {
                    showToast('সমস্যা হয়েছে: ' + (data.error || 'unknown'), true);
                }
            } catch (e) {
                showToast('সার্ভার এরর', true);
            }
        });

        function resetForm() {
            form.reset();
            document.getElementById('channelId').value = '';
            document.getElementById('isActive').checked = true;
            formTitle.textContent = 'Add Channel';
            submitBtn.textContent = 'Add Channel';
            cancelEditBtn.style.display = 'none';
        }

        cancelEditBtn.addEventListener('click', resetForm);

        // ── TABLE ACTIONS (edit / delete / toggle) ──
        tableBody.addEventListener('click', async (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            const action = btn.dataset.action;
            const id = btn.dataset.id;
            const channel = allChannels.find(c => String(c.id) === String(id));
            if (!channel) return;

            if (action === 'edit') {
                document.getElementById('channelId').value = channel.id;
                document.getElementById('name').value = channel.name;
                document.getElementById('url').value = channel.url;
                document.getElementById('logo').value = channel.logo || '';
                document.getElementById('category').value = channel.category;
                document.getElementById('sortOrder').value = channel.sort_order;
                document.getElementById('isPopular').checked = !!channel.is_popular;
                document.getElementById('isActive').checked = !!channel.is_active;
                formTitle.textContent = 'Edit Channel — ' + channel.name;
                submitBtn.textContent = 'Save Changes';
                cancelEditBtn.style.display = 'inline-block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            if (action === 'delete') {
                if (!confirm(`"${channel.name}" চ্যানেলটি ডিলিট করতে চান?`)) return;
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('id', id);
                const res = await fetch('api.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    showToast('চ্যানেল ডিলিট হয়েছে');
                    loadChannels();
                } else {
                    showToast('ডিলিট করা যায়নি', true);
                }
                return;
            }

            if (action === 'toggle_popular' || action === 'toggle_active') {
                const fd = new FormData();
                fd.append('action', action);
                fd.append('id', id);
                const res = await fetch('api.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    loadChannels();
                } else {
                    showToast('আপডেট করা যায়নি', true);
                }
            }
        });

        loadChannels();
    </script>
</body>

</html>