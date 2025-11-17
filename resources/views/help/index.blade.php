{{-- resources/views/help/index.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    /* ---- LAYOUT ---- */
    #pdfViewerArea {
        height: calc(100vh - 260px);
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        background: #f6f7f8;
        padding: 12px;
        border-radius: 6px;
    }

    .pages-container {
        display: flex;
        flex-direction: column;
        gap: 14px;
        align-items: center;
        padding-bottom: 12px;
    }

    .pdf-page {
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        border-radius: 4px;
        max-width: 900px;
        width: 100%;
        overflow: hidden;
    }

    .pdf-page canvas {
        width: 100%;
        height: auto;
        display: block;
    }

    .pdf-loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }

    /* Help layout */
    .help-section {
        border: 1px solid #eef0f2;
        border-radius: 6px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .help-section .section-header {
        background: #fafbfd;
        padding: 10px 12px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        cursor: pointer;
    }
    .help-section .section-body {
        padding: 8px 12px;
        display: none;
        background: #fff;
    }
    .help-topic {
        padding: 8px;
        border-radius: 4px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        cursor: pointer;
    }
    .help-topic:hover { background: #f6f9ff; }
    .topic-title { font-weight: 600; }
    .topic-meta { color: #6c757d; font-size: 0.9rem; }
    .sublist { margin-left: 8px; }

    .active-topic { background:#eef7ff; }

    /* small helpers */
    .zoom-controls .btn { min-width: 34px; }
    .pdf-actions { display:flex; gap:8px; align-items:center; }
</style>

<div class="container-fluid">
    <?php $page_title = "Help"; $sub_title = "Support"; ?>

    <!-- Page Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">{{ $sub_title }}</a></li>
                        <li class="breadcrumb-item active">{{ $page_title }}</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ $page_title }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left: Help Sections -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Help Topics</h5>

                    <!-- <form method="GET" action="{{ route('help.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-sm me-2" placeholder="Search Help">
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('help.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form> -->
                </div>

                <div class="card-body">
                    {{-- Sections: collapsed accordion style --}}
                    <div id="helpSections">

                        <!-- Subscriptions -->
                        <div class="help-section" data-section="subscriptions">
                            <div class="section-header">
                                <div><strong>Subscriptions</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="sub_create">
                                    <div>
                                        <div class="topic-title">How to create a subscription</div>
                                        <div class="topic-meta">Subscriptions / Create</div>
                                    </div>
                                    <div class="badge bg-secondary">S-001</div>
                                </div>

                                <div class="help-topic" data-topic="sub_assign">
                                    <div>
                                        <div class="topic-title">Assign subscription to client</div>
                                        <div class="topic-meta">Subscriptions / Assign</div>
                                    </div>
                                    <div class="badge bg-secondary">S-002</div>
                                </div>

                                <div class="help-topic" data-topic="sub_billing">
                                    <div>
                                        <div class="topic-title">Billing & invoices</div>
                                        <div class="topic-meta">Subscriptions / Billing</div>
                                    </div>
                                    <div class="badge bg-secondary">S-003</div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory -->
                        <div class="help-section" data-section="inventory">
                            <div class="section-header">
                                <div><strong>Inventory</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="inv_add">
                                    <div>
                                        <div class="topic-title">Add new inventory item (box)</div>
                                        <div class="topic-meta">Inventory / Add</div>
                                    </div>
                                    <div class="badge bg-secondary">I-001</div>
                                </div>

                                <div class="help-topic" data-topic="inv_list">
                                    <div>
                                        <div class="topic-title">Inventory list & filters</div>
                                        <div class="topic-meta">Inventory / List</div>
                                    </div>
                                    <div class="badge bg-secondary">I-002</div>
                                </div>

                                <div class="help-topic" data-topic="inv_packages">
                                    <div>
                                        <div class="topic-title">Inventory packages (allocate packages to boxes)</div>
                                        <div class="topic-meta">Inventory / Packages</div>
                                    </div>
                                    <div class="badge bg-secondary">I-003</div>
                                </div>
                            </div>
                        </div>

                        <!-- Channels -->
                        <div class="help-section" data-section="channels">
                            <div class="section-header">
                                <div><strong>Channels</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="ch_add">
                                    <div>
                                        <div class="topic-title">Add a channel</div>
                                        <div class="topic-meta">Channels / Add</div>
                                    </div>
                                    <div class="badge bg-secondary">C-001</div>
                                </div>

                                <div class="help-topic" data-topic="ch_manage">
                                    <div>
                                        <div class="topic-title">Manage channels (edit, search, status)</div>
                                        <div class="topic-meta">Channels / Manage</div>
                                    </div>
                                    <div class="badge bg-secondary">C-002</div>
                                </div>

                                <div class="help-topic" data-topic="ch_sort">
                                    <div>
                                        <div class="topic-title">Channel sorting & mapping</div>
                                        <div class="topic-meta">Channels / Ordering</div>
                                    </div>
                                    <div class="badge bg-secondary">C-003</div>
                                </div>
                            </div>
                        </div>

                        <!-- Packages -->
                        <div class="help-section" data-section="packages">
                            <div class="section-header">
                                <div><strong>Packages</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="pkg_create">
                                    <div>
                                        <div class="topic-title">Create a package</div>
                                        <div class="topic-meta">Packages / Create</div>
                                    </div>
                                    <div class="badge bg-secondary">P-001</div>
                                </div>

                                <div class="help-topic" data-topic="pkg_assign_client">
                                    <div>
                                        <div class="topic-title">Assign package to client</div>
                                        <div class="topic-meta">Packages / Assign</div>
                                    </div>
                                    <div class="badge bg-secondary">P-002</div>
                                </div>

                                <div class="help-topic" data-topic="pkg_manage">
                                    <div>
                                        <div class="topic-title">Manage package contents</div>
                                        <div class="topic-meta">Packages / Edit</div>
                                    </div>
                                    <div class="badge bg-secondary">P-003</div>
                                </div>
                            </div>
                        </div>

                        <!-- Allocations -->
                        <div class="help-section" data-section="allocations">
                            <div class="section-header">
                                <div><strong>Allocations</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="alloc_create">
                                    <div>
                                        <div class="topic-title">Create allocation</div>
                                        <div class="topic-meta">Allocations / Create</div>
                                    </div>
                                    <div class="badge bg-secondary">A-001</div>
                                </div>

                                <div class="help-topic" data-topic="alloc_manage">
                                    <div>
                                        <div class="topic-title">View & modify allocations</div>
                                        <div class="topic-meta">Allocations / Manage</div>
                                    </div>
                                    <div class="badge bg-secondary">A-002</div>
                                </div>
                            </div>
                        </div>

                        <!-- Utilities -->
                        <div class="help-section" data-section="utilities">
                            <div class="section-header">
                                <div><strong>Utilities</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="util_import">
                                    <div>
                                        <div class="topic-title">Import data (Excel)</div>
                                        <div class="topic-meta">Utilities / Import</div>
                                    </div>
                                    <div class="badge bg-secondary">U-001</div>
                                </div>

                                <div class="help-topic" data-topic="util_backup">
                                    <div>
                                        <div class="topic-title">Database backup with cron</div>
                                        <div class="topic-meta">Utilities / Backup</div>
                                    </div>
                                    <div class="badge bg-secondary">U-002</div>
                                </div>

                                <div class="help-topic" data-topic="util_logs">
                                    <div>
                                        <div class="topic-title">View system logs</div>
                                        <div class="topic-meta">Utilities / Logs</div>
                                    </div>
                                    <div class="badge bg-secondary">U-003</div>
                                </div>
                            </div>
                        </div>

                        <!-- Reports -->
                        <div class="help-section" data-section="reports">
                            <div class="section-header">
                                <div><strong>Reports</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="sublist">
                                    <div class="help-topic" data-topic="rep_installed">
                                        <div>
                                            <div class="topic-title">Installed Boxes</div>
                                            <div class="topic-meta">Reports / Installed Boxes</div>
                                        </div>
                                        <div class="badge bg-secondary">R-001</div>
                                    </div>
                                    <div class="help-topic" data-topic="rep_live">
                                        <div>
                                            <div class="topic-title">Live Boxes</div>
                                            <div class="topic-meta">Reports / Live Boxes</div>
                                        </div>
                                        <div class="badge bg-secondary">R-002</div>
                                    </div>
                                    <div class="help-topic" data-topic="rep_channels">
                                        <div>
                                            <div class="topic-title">Channels Report</div>
                                            <div class="topic-meta">Reports / Channels</div>
                                        </div>
                                        <div class="badge bg-secondary">R-003</div>
                                    </div>
                                    <div class="help-topic" data-topic="rep_packages">
                                        <div>
                                            <div class="topic-title">Packages Report</div>
                                            <div class="topic-meta">Reports / Packages</div>
                                        </div>
                                        <div class="badge bg-secondary">R-004</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin -->
                        <div class="help-section" data-section="admin">
                            <div class="section-header">
                                <div><strong>Admin / Manage</strong></div>
                                <div class="toggle-indicator">+</div>
                            </div>
                            <div class="section-body">
                                <div class="help-topic" data-topic="admin_users">
                                    <div>
                                        <div class="topic-title">Manage Users</div>
                                        <div class="topic-meta">Admin / Users</div>
                                    </div>
                                    <div class="badge bg-secondary">ADM-001</div>
                                </div>

                                <div class="help-topic" data-topic="admin_perms">
                                    <div>
                                        <div class="topic-title">Manage Permissions & Roles</div>
                                        <div class="topic-meta">Admin / Permissions</div>
                                    </div>
                                    <div class="badge bg-secondary">ADM-002</div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- helpSections -->
                </div>
            </div>
        </div>

        <!-- Right: PDF Viewer + Help Details -->
        <div class="col-md-4">
            <!-- <div class="card mb-3">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0">Document Viewer</h6>
                        <small class="text-muted ms-2">(Preview only)</small>
                    </div>

                    <div class="pdf-actions">
                        <div class="zoom-controls btn-group" role="group" aria-label="zoom">
                            <button id="zoomOut" class="btn btn-sm btn-outline-secondary">-</button>
                            <button id="zoomReset" class="btn btn-sm btn-outline-secondary">100%</button>
                            <button id="zoomIn" class="btn btn-sm btn-outline-secondary">+</button>
                        </div>

                        <a href="{{ route('help.view') }}" target="_blank" class="btn btn-sm btn-link">Open in new tab</a>
                    </div>
                </div>

                <div class="card-body p-2">
                    <div id="pdfViewerArea">
                        <div id="loadingMessage" class="pdf-loading">Loading PDF…</div>
                        <div id="pagesContainer" class="pages-container"></div>
                    </div>
                </div>
            </div> -->

            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Help Details</h6>
                </div>
                <div class="card-body" id="helpDetail">
                    <p class="text-muted">Select a topic to view details here. You can expand/collapse sections on the left.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- pdf.js --}}
<script src="{{ asset('assets/js/pdf.min.js') }}"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/js/pdf.worker.min.js') }}";
</script>

<script>
/*
  PDF Viewer
*/
(function() {
    const pdfUrl = "{{ route('help.view') }}";
    const pagesContainer = document.getElementById("pagesContainer");
    const loadingMessage = document.getElementById("loadingMessage");

    let scale = 1.0;
    const MIN_SCALE = 0.5;
    const MAX_SCALE = 3.0;
    const STEP = 0.1;

    const zoomInBtn = document.getElementById("zoomIn");
    const zoomOutBtn = document.getElementById("zoomOut");
    const zoomResetBtn = document.getElementById("zoomReset");

    zoomInBtn.onclick = () => changeScale(scale + STEP);
    zoomOutBtn.onclick = () => changeScale(scale - STEP);
    zoomResetBtn.onclick = () => changeScale(1.0);

    function changeScale(newScale) {
        newScale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, newScale));
        scale = newScale;
        zoomResetBtn.innerHTML = Math.round(scale * 100) + "%";

        const canvases = pagesContainer.querySelectorAll("canvas");
        canvases.forEach(canvas => {
            const pageNum = parseInt(canvas.getAttribute("data-page"));
            renderPage(pageNum, canvas);
        });
    }

    let cachedPdf = null;
    pdfjsLib.getDocument(pdfUrl).promise.then((pdf) => {
        cachedPdf = pdf;
        loadingMessage.style.display = "none";

        for (let i = 1; i <= pdf.numPages; i++) {
            const wrapper = document.createElement("div");
            wrapper.className = "pdf-page";

            const canvas = document.createElement("canvas");
            canvas.setAttribute("data-page", i);
            wrapper.appendChild(canvas);

            pagesContainer.appendChild(wrapper);

            renderPage(i, canvas);
        }
    }).catch(err => {
        loadingMessage.innerHTML = "Failed to load PDF.";
        console.error(err);
    });

    function renderPage(pageNumber, canvas) {
        if (!cachedPdf) {
            setTimeout(() => renderPage(pageNumber, canvas), 100);
            return;
        }
        cachedPdf.getPage(pageNumber).then((page) => {
            const viewport0 = page.getViewport({ scale: 1.0 });
            const maxWidth = Math.min(900, pagesContainer.clientWidth - 24);
            const newScale = scale * (maxWidth / viewport0.width);

            const viewport = page.getViewport({ scale: newScale });
            const ctx = canvas.getContext("2d");

            canvas.width = Math.round(viewport.width * window.devicePixelRatio);
            canvas.height = Math.round(viewport.height * window.devicePixelRatio);
            ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);

            const renderContext = { canvasContext: ctx, viewport: viewport };
            page.render(renderContext).catch(err => console.error("Render error:", err));
        }).catch(err => console.error("Page show error:", err));
    }

    let resizeTimer = null;
    window.addEventListener("resize", () => {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const canvases = pagesContainer.querySelectorAll("canvas");
            canvases.forEach((canvas) => {
                const pageNum = parseInt(canvas.getAttribute("data-page"));
                renderPage(pageNum, canvas);
            });
        }, 150);
    });

})();
</script>

<script>
/*
  Detailed topics data for all sections requested.
  Edit these HTML strings to change content later, or load from server.
*/
const topicsData = {
  // Subscriptions
  sub_create: {
    title: "How to create a subscription",
    id: "S-001",
    content: `<p>Navigate to <strong>Subscriptions → Add Subscription</strong>. Fill the form:</p>
      <ol>
        <li><strong>Plan Name</strong> — e.g., "Monthly Basic".</li>
        <li><strong>Price</strong> — numeric value and currency.</li>
        <li><strong>Billing cycle</strong> — monthly/quarterly/yearly.</li>
        <li><strong>Description</strong> — optional details.</li>
      </ol>
      <p>Click <strong>Save</strong>. The new plan appears in the subscription list. Use the Actions column to edit or deactivate.</p>`
  },
  sub_assign: {
    title: "Assign subscription to client",
    id: "S-002",
    content: `<p>Open the <strong>Clients</strong> page, select a client and go to <strong>Subscriptions</strong> tab. Click <strong>Assign</strong>, choose the plan, set start/end date and save. Notifications will be sent if billing is enabled.</p>`
  },
  sub_billing: {
    title: "Billing & invoices",
    id: "S-003",
    content: `<p>Billing module allows invoice generation and viewing. Steps:</p>
      <ol>
        <li>Open <strong>Billing → Invoices</strong>.</li>
        <li>Filter by client or date range.</li>
        <li>Use <strong>Generate</strong> to create invoices for manual charges.</li>
      </ol>
      <p>Recurring invoices are created automatically according to subscription cycle if auto-billing is enabled.</p>`
  },

  // Inventory
  inv_add: {
    title: "Add new inventory item (box)",
    id: "I-001",
    content: `<p>Go to <strong>Inventory → Add Box</strong>. Required fields:</p>
      <ul>
        <li><strong>Model</strong>, <strong>Serial No</strong>, <strong>MAC ID</strong>, <strong>Box IP</strong></li>
      </ul>
      <p>Optionally attach it to a client immediately by choosing a client in the assignment section. Click <strong>Save</strong>.</p>`
  },
  inv_list: {
    title: "Inventory list & filters",
    id: "I-002",
    content: `<p>Inventory page displays all boxes. Important controls:</p>
      <ul>
        <li><strong>Search</strong> — quick search by any field.</li>
        <li><strong>Filters</strong> — filter by assigned/unassigned, firmware, client, etc.</li>
        <li><strong>Box details</strong> — click a row to see details on the right panel.</li>
      </ul>`
  },
  inv_packages: {
    title: "Inventory packages (allocate packages to boxes)",
    id: "I-003",
    content: `<p>From Inventory → Inventory Packages you can assign a package to a box:</p>
      <ol>
        <li>Select the box → Click <strong>Edit</strong> → Choose <strong>Allocated Packages</strong>.</li>
        <li>Save to update the box and push package configuration to the device (when device polls or on next sync).</li>
      </ol>`
  },

  // Channels
  ch_add: {
    title: "Add a channel",
    id: "C-001",
    content: `<p>Navigate to <strong>Channels → Add Channel</strong>. Provide:</p>
      <ul>
        <li><strong>Channel name</strong></li>
        <li><strong>Broadcaster/source</strong></li>
        <li><strong>Genre / Resolution / Language</strong></li>
        <li>Optionally attach to packages directly.</li>
      </ul>
      <p>Click <strong>Save</strong>.</p>`
  },
  ch_manage: {
    title: "Manage channels (edit, search, status)",
    id: "C-002",
    content: `<p>Channels list supports:</p>
      <ul>
        <li><strong>Edit</strong> channel metadata.</li>
        <li><strong>Search</strong> by name or broadcaster.</li>
        <li><strong>Toggle status</strong> to enable/disable a channel.</li>
      </ul>`
  },
  ch_sort: {
    title: "Channel sorting & mapping",
    id: "C-003",
    content: `<p>Open channel ordering UI to set display order for each package. Steps:</p>
      <ol>
        <li>Open <strong>Channels → Sort / Order</strong>.</li>
        <li>Drag-and-drop channels to the desired order or set numeric order values.</li>
        <li>Save. Packages using this ordering will reflect the change.</li>
      </ol>`
  },

  // Packages
  pkg_create: {
    title: "Create a package",
    id: "P-001",
    content: `<p>Packages → Add Package. Fields:</p>
      <ul>
        <li><strong>Name</strong>, <strong>Description</strong></li>
        <li>Select channels to include (search & multi-select)</li>
        <li><strong>Price / Status</strong></li>
      </ul>
      <p>Save to create package. Use <strong>Show all</strong> to view many channels in the package.</p>`
  },
  pkg_assign_client: {
    title: "Assign package to client",
    id: "P-002",
    content: `<p>From <strong>Clients → Packages</strong>, choose a client, click <strong>Edit</strong>, tick packages to assign and set start/end date. Save and the client will be billed per their subscription (if applicable).</p>`
  },
  pkg_manage: {
    title: "Manage package contents",
    id: "P-003",
    content: `<p>Edit a package to add/remove channels or change price. Note:</p>
      <ul>
        <li>Changes apply to new assignments immediately.</li>
        <li>Existing active assignments may need a manual refresh on the client devices.</li>
      </ul>`
  },

  // Allocations
  alloc_create: {
    title: "Create allocation",
    id: "A-001",
    content: `<p>Allocations are used to reserve boxes or resources for a client or installer. To create:</p>
      <ol>
        <li>Go to <strong>Allocations → Create Allocation</strong>.</li>
        <li>Select box(es), target client or location and set allocation dates.</li>
        <li>Save. Allocation status will appear in the Allocations list.</li>
      </ol>`
  },
  alloc_manage: {
    title: "View & modify allocations",
    id: "A-002",
    content: `<p>Allocations list shows current/reserved boxes. Use <strong>Edit</strong> to reassign or cancel. Use filters to find allocations by client or date.</p>`
  },

  // Utilities
  util_import: {
    title: "Import data (Excel)",
    id: "U-001",
    content: `<p>Utilities → Import allows bulk imports for Inventory/Channels. Steps:</p>
      <ol>
        <li>Download <strong>Sample File</strong> to see required columns.</li>
        <li>Prepare your Excel (.xlsx/.xls/.csv) matching sample headings.</li>
        <li>Use <strong>Choose file</strong> → <strong>Import</strong>. Check import logs for errors.</li>
      </ol>`
  },
  util_backup: {
    title: "Database backup with cron",
    id: "U-002",
    content: `<p>Example approach to backup DB from Server A (rocky linux) to Server B daily at 00:01:</p>
      <pre style="background:#f7f7f7;padding:8px;border-radius:4px;">#!/bin/bash
# backup_db.sh
DATE=$(date +%F)
mysqldump -u DBUSER -p'PASSWORD' DB_NAME > /tmp/db_backup_$DATE.sql
scp /tmp/db_backup_$DATE.sql user@serverB:/backups/
rm /tmp/db_backup_$DATE.sql
</pre>
      <p>Add cron job (<code>crontab -e</code>):</p>
      <pre style="background:#f7f7f7;padding:8px;border-radius:4px;">1 0 * * * /path/to/backup_db.sh &gt;/dev/null 2&gt;&1</pre>
      <p>Ensure SSH key access is set between Server A and Server B and secure your DB password.</p>`
  },
  util_logs: {
    title: "View system logs",
    id: "U-003",
    content: `<p>Open Utilities → Logs. Use filters for date/time and log level. For deeper analysis, export logs and open in your preferred log viewer.</p>`
  },

  // Reports
  rep_installed: {
    title: "Installed Boxes",
    id: "R-001",
    content: `<p>This report lists boxes that have been installed along with installation date and client. Use export to CSV for offline records. Columns include Box ID, Serial No, Client, Installed On, Location.</p>`
  },
  rep_live: {
    title: "Live Boxes",
    id: "R-002",
    content: `<p>Shows currently online boxes with last-seen timestamp and IP. Helpful to diagnose connectivity issues; click a box to view details or use actions to reboot or send commands if supported.</p>`
  },
  rep_channels: {
    title: "Channels Report",
    id: "R-003",
    content: `<p>Channel usage statistics, most-watched channels, and channel health (active/inactive). Filters available by package and date range.</p>`
  },
  rep_packages: {
    title: "Packages Report",
    id: "R-004",
    content: `<p>Summary of package subscriptions, revenue per package, and active counts. Useful for business reporting and forecasting.</p>`
  },

  // Admin
  admin_users: {
    title: "Manage Users",
    id: "ADM-001",
    content: `<p>Admin → Users: create, edit, and deactivate user accounts. Steps to add a user:</p>
      <ol>
        <li>Click <strong>Add User</strong>.</li>
        <li>Fill in name, email, role, and password.</li>
        <li>Save. Use Edit to change roles later.</li>
      </ol>`
  },
  admin_perms: {
    title: "Manage Permissions & Roles",
    id: "ADM-002",
    content: `<p>Roles & Permissions allow you to group permissions and assign them to users. Steps:</p>
      <ol>
        <li>Go to <strong>Admin → Permissions</strong>.</li>
        <li>Create roles (e.g., Admin, Manager, Installer) and assign permission sets.</li>
        <li>Assign roles to users from the Users screen.</li>
      </ol>`
  }
};

/* DOM interactivity */
document.addEventListener('DOMContentLoaded', function() {
  // Toggle sections open/close
  document.querySelectorAll('.help-section').forEach(section => {
    const header = section.querySelector('.section-header');
    header.addEventListener('click', () => {
      const body = section.querySelector('.section-body');
      const indicator = header.querySelector('.toggle-indicator');
      if (body.style.display === 'block') {
        body.style.display = 'none';
        indicator.textContent = '+';
      } else {
        body.style.display = 'block';
        indicator.textContent = '−';
      }
    });
  });

  // Topic clicks load details
  document.querySelectorAll('.help-topic').forEach(topicEl => {
    topicEl.addEventListener('click', () => {
      const topicKey = topicEl.getAttribute('data-topic');
      showTopicDetail(topicKey);
      // visually highlight selected topic
      document.querySelectorAll('.help-topic').forEach(t => t.classList.remove('active-topic'));
      topicEl.classList.add('active-topic');
      // open right pane scroll to top
      document.getElementById('helpDetail').scrollTop = 0;
    });
  });

  // Open first section by default and show first topic
  const firstSection = document.querySelector('.help-section');
  if (firstSection) {
    firstSection.querySelector('.section-header').click();
    const firstTopic = firstSection.querySelector('.help-topic');
    if (firstTopic) firstTopic.click();
  }
});

// Display topic details in right pane
function showTopicDetail(key) {
  const detailBox = document.getElementById('helpDetail');
  const topic = topicsData[key];
  if (!topic) {
    detailBox.innerHTML = `<p class="text-muted">Details not available for this topic.</p>`;
    return;
  }
  detailBox.innerHTML = `
    <div>
      <h5>${topic.title} <small class="text-muted">(${topic.id})</small></h5>
      <div class="mt-2">${topic.content}</div>
    </div>
  `;
}
</script>

@endsection
