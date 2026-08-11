<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System — Cloud Dashboard</title>
    <meta name="description" content="Face recognition attendance system cloud control panel.">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-title h1 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .nav-tabs {
            display: flex;
            gap: 1rem;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: var(--text-main);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .tab-btn.active {
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.1);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-logout {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .btn-logout:hover {
            color: var(--danger);
            border-color: var(--danger);
        }

        main {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--text-muted);
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .badge-in { background-color: rgba(34, 197, 94, 0.15); color: var(--success); }
        .badge-out { background-color: rgba(239, 68, 68, 0.15); color: var(--danger); }

        .thumb-img {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid var(--border);
        }

        .btn-view-more {
            display: block;
            width: 100%;
            text-align: center;
            padding: 0.6rem;
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--primary);
            border: 1px solid var(--border);
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            margin-top: 1rem;
            cursor: pointer;
        }

        .btn-view-more:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        input, select {
            padding: 0.6rem 0.8rem;
            background-color: #0f172a;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            outline: none;
        }

        button.btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        button.btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            background-color: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .modal-content img {
            max-width: 100%;
            max-height: 80vh;
            display: block;
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
        }

        .history-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .sub-nav {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
        }

        .sub-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            cursor: pointer;
            border-radius: 4px;
        }

        .sub-btn.active {
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-title">
            <h1>Attendance System Cloud</h1>
        </div>

        <nav class="nav-tabs">
            <button class="tab-btn active" onclick="switchTab('live')">Live Dashboard</button>
            <button class="tab-btn" onclick="switchTab('cameras')">Cameras</button>
            <button class="tab-btn" onclick="switchTab('history')">History & Sightings</button>
            <button class="tab-btn" onclick="switchTab('shifts')">Guard Shifts</button>
            <button class="tab-btn" onclick="switchTab('register')">Face Registration</button>
        </nav>

        <div class="user-actions">
            <button onclick="logout()" class="btn-logout">Logout</button>
        </div>
    </header>

    <main>
        <!-- LIVE DASHBOARD TAB -->
        <div id="tab-live" class="tab-content active">
            <div class="dashboard-grid">
                <div>
                    <div class="card" style="border-left: 4px solid var(--primary);">
                        <div class="card-header" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">
                            <span class="card-title">🟢 Cloud Sync Active</span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">
                            Detection engine running on local Windows machine. Real-time events & snapshot frames are pushed automatically to this cloud dashboard.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Today's Latest Logs</span>
                            <span id="log-count-text" style="font-size: 0.85rem; color: var(--text-muted);"></span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Snapshot</th>
                                    <th>Name</th>
                                    <th>Event</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody id="today-logs-body">
                                <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Loading logs...</td></tr>
                            </tbody>
                        </table>
                        <button class="btn-view-more" onclick="switchTab('history')">View Full History &rarr;</button>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Currently Inside</span>
                        </div>
                        <ul id="inside-list" style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                            <li style="color: var(--text-muted);">Loading...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- CAMERAS TAB -->
        <div id="tab-cameras" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Add / Update RTSP Cameras</span>
                </div>
                <form id="camera-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Camera Name</label>
                            <input type="text" id="cam-name" required placeholder="e.g. Front Gate Camera">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>RTSP Stream URL</label>
                            <input type="text" id="cam-url" required placeholder="rtsp://admin:password@192.168.1.45:554/cam/realmonitor?channel=1&subtype=0">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Add Camera</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Configured Cameras</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>RTSP URL</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="configured-cameras-body">
                        <tr><td colspan="5" style="text-align: center;">Loading cameras...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- HISTORY TAB -->
        <div id="tab-history" class="tab-content">
            <div class="sub-nav">
                <button class="sub-btn active" id="sub-btn-collective" onclick="switchHistorySubTab('collective')">Collective History</button>
                <button class="sub-btn" id="sub-btn-person" onclick="switchHistorySubTab('person')">Specific Person History</button>
            </div>

            <div id="history-view-collective">
                <div class="card">
                    <div class="history-controls">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" id="hist-date-from">
                        </div>
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" id="hist-date-to">
                        </div>
                        <button class="btn-primary" onclick="loadCollectiveHistory()">Filter Logs</button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Snapshot</th>
                                <th>Name</th>
                                <th>Event</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="collective-history-body">
                            <tr><td colspan="4" style="text-align: center;">Select dates and click filter.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="history-view-person" style="display: none;">
                <div class="card">
                    <div class="history-controls">
                        <div class="form-group">
                            <label>Select Guard / Person</label>
                            <select id="hist-person-select">
                                <option value="">Loading people...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" id="hist-person-date-from">
                        </div>
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" id="hist-person-date-to">
                        </div>
                        <button class="btn-primary" onclick="loadPersonHistory()">View Logs & Sightings</button>
                    </div>

                    <h3 style="margin-bottom: 0.5rem; font-size: 1rem;">Attendance Logs (IN / OUT)</h3>
                    <table style="margin-bottom: 1.5rem;">
                        <thead>
                            <tr>
                                <th>Snapshot</th>
                                <th>Event</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="person-logs-body">
                            <tr><td colspan="3" style="text-align: center;">Select a person to view history.</td></tr>
                        </tbody>
                    </table>

                    <h3 style="margin-bottom: 0.5rem; font-size: 1rem;">Mid-Shift Frame Sightings</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Snapshot</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="person-sightings-body">
                            <tr><td colspan="2" style="text-align: center;">Select a person to view sightings.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SHIFTS TAB -->
        <div id="tab-shifts" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Assign Guard Shift</span>
                </div>
                <form id="shift-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Guard Name</label>
                            <select id="shift-person-select" required>
                                <option value="">Loading people...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shift Start Time</label>
                            <input type="time" id="shift-start" required>
                        </div>
                        <div class="form-group">
                            <label>Shift End Time</label>
                            <input type="time" id="shift-end" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Save Shift</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Active Guard Shifts</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Guard Name</th>
                            <th>Shift Start</th>
                            <th>Shift End</th>
                            <th>Active Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="shifts-list-body">
                        <tr><td colspan="5" style="text-align: center;">Loading shifts...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- REGISTER TAB -->
        <div id="tab-register" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Register New Face (Requires at least 3 Images)</span>
                </div>
                <form id="register-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Person Full Name</label>
                            <input type="text" id="reg-name" required placeholder="e.g. John Doe">
                        </div>
                        <div class="form-group">
                            <label>Upload Face Photo</label>
                            <input type="file" id="reg-file" accept="image/*" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Upload Face Photo</button>
                </form>
                <div id="reg-msg" style="margin-top: 1rem; font-size: 0.9rem;"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">Registered People</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Uploaded Images</th>
                            <th>Sync Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="registered-people-body">
                        <tr><td colspan="5" style="text-align: center;">Loading registered people...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div id="img-modal" class="modal" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <img id="modal-img" src="" alt="Full Snapshot">
        </div>
    </div>

    <script>
        const todayStr = new Date().toISOString().split('T')[0];
        document.getElementById('hist-date-from').value = todayStr;
        document.getElementById('hist-date-to').value = todayStr;
        document.getElementById('hist-person-date-from').value = todayStr;
        document.getElementById('hist-person-date-to').value = todayStr;

        async function logout() {
            try { await fetch('/attendance/api/logout', { method: 'POST' }); } catch (e) {}
            window.location.href = '/attendance/login';
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            const btn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick').includes(tabId));
            if (btn) btn.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');

            if (tabId === 'live') loadLiveDashboard();
            if (tabId === 'cameras') loadConfiguredCameras();
            if (tabId === 'history') loadPeopleDropdowns();
            if (tabId === 'shifts') { loadPeopleDropdowns(); loadShifts(); }
            if (tabId === 'register') loadRegisteredPeople();
        }

        function switchHistorySubTab(sub) {
            document.getElementById('sub-btn-collective').classList.toggle('active', sub === 'collective');
            document.getElementById('sub-btn-person').classList.toggle('active', sub === 'person');
            document.getElementById('history-view-collective').style.display = sub === 'collective' ? 'block' : 'none';
            document.getElementById('history-view-person').style.display = sub === 'person' ? 'block' : 'none';
        }

        function openModal(imgSrc) {
            document.getElementById('modal-img').src = imgSrc;
            document.getElementById('img-modal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('img-modal').style.display = 'none';
        }

        async function loadLiveDashboard() {
            try {
                const [todayRes, insideRes] = await Promise.all([
                    fetch('/attendance/api/attendance/today?limit=20'),
                    fetch('/attendance/api/attendance/inside')
                ]);

                if (todayRes.ok) {
                    const data = await todayRes.json();
                    document.getElementById('log-count-text').textContent = `Showing ${data.records.length} of ${data.total_count} total logs today`;
                    const body = document.getElementById('today-logs-body');
                    body.innerHTML = data.records.length === 0 ? '<tr><td colspan="4" style="text-align: center;">No attendance logs today.</td></tr>' :
                        data.records.map(r => `
                            <tr>
                                <td>${r.snapshot_path ? `<img src="/attendance/snapshots/${r.snapshot_path}" class="thumb-img" onclick="openModal('/attendance/snapshots/${r.snapshot_path}')">` : 'N/A'}</td>
                                <td><strong>${r.person_name}</strong></td>
                                <td><span class="badge ${r.event_type === 'IN' ? 'badge-in' : 'badge-out'}">${r.event_type}</span></td>
                                <td>${r.timestamp}</td>
                            </tr>
                        `).join('');
                }

                if (insideRes.ok) {
                    const data = await insideRes.json();
                    const list = document.getElementById('inside-list');
                    list.innerHTML = data.inside.length === 0 ? '<li style="color: var(--text-muted);">No one currently inside</li>' :
                        data.inside.map(name => `<li style="padding: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 4px; font-weight: 600;">🟢 ${name}</li>`).join('');
                }
            } catch (err) { console.error(err); }
        }

        async function loadConfiguredCameras() {
            try {
                const res = await fetch('/attendance/api/cameras/config');
                if (res.ok) {
                    const data = await res.json();
                    const body = document.getElementById('configured-cameras-body');
                    body.innerHTML = data.cameras.length === 0 ? '<tr><td colspan="5" style="text-align: center;">No cameras configured.</td></tr>' :
                        data.cameras.map(c => `
                            <tr>
                                <td>#${c.id}</td>
                                <td><strong>${c.name}</strong></td>
                                <td style="font-family: monospace; font-size: 0.85rem;">${c.url.replace(/:\/\/[^:]+:[^@]+@/, '://***:***@')}</td>
                                <td>${c.enabled ? '<span style="color: var(--success);">Active</span>' : '<span style="color: var(--text-muted);">Disabled</span>'}</td>
                                <td><button onclick="deleteCamera(${c.id})" style="color: var(--danger); background:none; border:none; cursor:pointer;">Delete</button></td>
                            </tr>
                        `).join('');
                }
            } catch (err) { console.error(err); }
        }

        document.getElementById('camera-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('cam-name').value;
            const url = document.getElementById('cam-url').value;
            await fetch('/attendance/api/cameras/config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, url, enabled: true })
            });
            document.getElementById('cam-name').value = '';
            document.getElementById('cam-url').value = '';
            loadConfiguredCameras();
        });

        async function deleteCamera(id) {
            if (confirm('Remove camera?')) {
                await fetch(`/attendance/api/cameras/config/${id}`, { method: 'DELETE' });
                loadConfiguredCameras();
            }
        }

        async function loadPeopleDropdowns() {
            try {
                const res = await fetch('/attendance/api/people');
                if (res.ok) {
                    const data = await res.json();
                    const opts = '<option value="">-- Select Person --</option>' + 
                        data.people.map(p => `<option value="${p.name}">${p.name}</option>`).join('');
                    document.getElementById('hist-person-select').innerHTML = opts;
                    document.getElementById('shift-person-select').innerHTML = opts;
                }
            } catch (err) { console.error(err); }
        }

        async function loadCollectiveHistory() {
            const from = document.getElementById('hist-date-from').value;
            const to = document.getElementById('hist-date-to').value;
            const res = await fetch(`/attendance/api/attendance/history?date_from=${from}&date_to=${to}`);
            if (res.ok) {
                const data = await res.json();
                const body = document.getElementById('collective-history-body');
                body.innerHTML = data.records.length === 0 ? '<tr><td colspan="4" style="text-align: center;">No logs found.</td></tr>' :
                    data.records.map(r => `
                        <tr>
                            <td>${r.snapshot_path ? `<img src="/attendance/snapshots/${r.snapshot_path}" class="thumb-img" onclick="openModal('/attendance/snapshots/${r.snapshot_path}')">` : 'N/A'}</td>
                            <td><strong>${r.person_name}</strong></td>
                            <td><span class="badge ${r.event_type === 'IN' ? 'badge-in' : 'badge-out'}">${r.event_type}</span></td>
                            <td>${r.timestamp}</td>
                        </tr>
                    `).join('');
            }
        }

        async function loadPersonHistory() {
            const name = document.getElementById('hist-person-select').value;
            if (!name) return alert('Select a person');
            const from = document.getElementById('hist-person-date-from').value;
            const to = document.getElementById('hist-person-date-to').value;
            const res = await fetch(`/attendance/api/attendance/person/${encodeURIComponent(name)}?date_from=${from}&date_to=${to}`);
            if (res.ok) {
                const data = await res.json();
                const logsBody = document.getElementById('person-logs-body');
                logsBody.innerHTML = data.records.length === 0 ? '<tr><td colspan="3" style="text-align: center;">No IN/OUT records found.</td></tr>' :
                    data.records.map(r => `
                        <tr>
                            <td>${r.snapshot_path ? `<img src="/attendance/snapshots/${r.snapshot_path}" class="thumb-img" onclick="openModal('/attendance/snapshots/${r.snapshot_path}')">` : 'N/A'}</td>
                            <td><span class="badge ${r.event_type === 'IN' ? 'badge-in' : 'badge-out'}">${r.event_type}</span></td>
                            <td>${r.timestamp}</td>
                        </tr>
                    `).join('');

                const sightBody = document.getElementById('person-sightings-body');
                sightBody.innerHTML = data.sightings.length === 0 ? '<tr><td colspan="2" style="text-align: center;">No sightings captured.</td></tr>' :
                    data.sightings.map(s => `
                        <tr>
                            <td>${s.snapshot_path ? `<img src="/attendance/snapshots/${s.snapshot_path}" class="thumb-img" onclick="openModal('/attendance/snapshots/${s.snapshot_path}')">` : 'N/A'}</td>
                            <td>${s.timestamp}</td>
                        </tr>
                    `).join('');
            }
        }

        async function loadShifts() {
            const res = await fetch('/attendance/api/shifts');
            if (res.ok) {
                const data = await res.json();
                const body = document.getElementById('shifts-list-body');
                body.innerHTML = data.shifts.length === 0 ? '<tr><td colspan="5" style="text-align: center;">No shifts assigned.</td></tr>' :
                    data.shifts.map(s => `
                        <tr>
                            <td><strong>${s.person_name}</strong></td>
                            <td>${s.shift_start}</td>
                            <td>${s.shift_end}</td>
                            <td>${s.days}</td>
                            <td><button onclick="removeShift('${s.person_name}')" style="color: var(--danger); background:none; border:none; cursor:pointer;">Remove</button></td>
                        </tr>
                    `).join('');
            }
        }

        document.getElementById('shift-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const person_name = document.getElementById('shift-person-select').value;
            const shift_start = document.getElementById('shift-start').value;
            const shift_end = document.getElementById('shift-end').value;
            await fetch('/attendance/api/shifts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ person_name, shift_start, shift_end })
            });
            loadShifts();
        });

        async function removeShift(name) {
            if (confirm(`Remove shift for ${name}?`)) {
                await fetch(`/attendance/api/shifts/${encodeURIComponent(name)}`, { method: 'DELETE' });
                loadShifts();
            }
        }

        loadLiveDashboard();
        setInterval(() => {
            if (document.getElementById('tab-live').classList.contains('active')) {
                loadLiveDashboard();
            }
        }, 5000);

        // Register form submit
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('reg-name').value;
            const file = document.getElementById('reg-file').files[0];
            const msg = document.getElementById('reg-msg');
            msg.textContent = 'Uploading face photo...';

            const formData = new FormData();
            formData.append('image', file);

            try {
                const res = await fetch(`/attendance/api/register?name=${encodeURIComponent(name)}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (res.ok) {
                    msg.style.color = data.is_complete ? 'var(--success)' : 'var(--warning)';
                    msg.textContent = `Photo uploaded! (${data.current_images_count}/3 required images). ${data.is_complete ? 'Syncing to local machine...' : 'Please upload ' + (3 - data.current_images_count) + ' more image(s).'}`;
                    loadRegisteredPeople();
                    document.getElementById('reg-file').value = '';
                } else {
                    msg.style.color = 'var(--danger)';
                    msg.textContent = data.detail || 'Upload failed';
                }
            } catch (err) { msg.textContent = 'Error uploading photo'; }
        });

        async function loadRegisteredPeople() {
            try {
                const res = await fetch('/attendance/api/people');
                if (res.ok) {
                    const data = await res.json();
                    const body = document.getElementById('registered-people-body');
                    body.innerHTML = data.people.length === 0 ? '<tr><td colspan="5" style="text-align: center;">No registered people.</td></tr>' :
                        data.people.map(p => {
                            let statusBadge = '';
                            if (p.status === 'ready') {
                                statusBadge = `<span class="badge" style="background-color: rgba(34, 197, 94, 0.15); color: var(--success);">Ready (Synced)</span>`;
                            } else if (p.status === 'pending') {
                                statusBadge = `<span class="badge" style="background-color: rgba(245, 158, 11, 0.15); color: var(--warning); animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">Syncing to Local...</span>`;
                            } else if (p.status === 'failed') {
                                statusBadge = `<span class="badge" style="background-color: rgba(239, 68, 68, 0.15); color: var(--danger);" title="${p.error_reason || ''}">Failed: ${p.error_reason || 'Sync error'}</span>`;
                            } else {
                                statusBadge = `<span class="badge" style="background-color: rgba(148, 163, 184, 0.15); color: var(--text-muted);">${p.face_count}/3 Images (Incomplete)</span>`;
                            }
                            return `
                                <tr>
                                    <td>${p.image_path ? `<img src="/attendance/data/${p.image_path}" class="thumb-img" onclick="openModal('/attendance/data/${p.image_path}')">` : 'N/A'}</td>
                                    <td><strong>${p.name}</strong></td>
                                    <td>${p.face_count} image(s)</td>
                                    <td>${statusBadge}</td>
                                    <td><button onclick="removePerson('${p.name}')" style="color: var(--danger); background:none; border:none; cursor:pointer;">Delete</button></td>
                                </tr>
                            `;
                        }).join('');
                }
            } catch (err) { console.error(err); }
        }

        async function removePerson(name) {
            if (confirm(`Delete ${name} and all registered face data?`)) {
                await fetch(`/attendance/api/people/${encodeURIComponent(name)}`, { method: 'DELETE' });
                loadRegisteredPeople();
            }
        }
    </script>
</body>
</html>
