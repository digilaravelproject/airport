@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <?php
        $page_title = "Help";
        $sub_title = "Support";
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#"><?php echo $sub_title ?></a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title ?></li>
                    </ol>
                </div>
                <h4 class="page-title"><?php echo $page_title ?></h4>
            </div>
        </div>
    </div>
    <!-- End Page Title -->

    <div class="row">
        <!-- Left: Help Topics Table -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Help Topics</h5>
                    <form method="GET" action="{{ route('help.index') }}" class="d-flex">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-sm me-2" placeholder="Search Help">
                        <button type="submit" class="btn btn-sm btn-primary me-2">Search</button>
                        <a href="{{ route('help.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Topic ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr onclick="openHelpDetail(1)" style="cursor: pointer;">
                                    <td>1</td>
                                    <td><span class="badge bg-secondary">HLP-001</span></td>
                                    <td>How to add a new client</td>
                                    <td>Clients</td>
                                </tr>
                                <tr onclick="openHelpDetail(2)" style="cursor: pointer;">
                                    <td>2</td>
                                    <td><span class="badge bg-secondary">HLP-002</span></td>
                                    <td>How to assign packages</td>
                                    <td>Packages</td>
                                </tr>
                                <tr onclick="openHelpDetail(3)" style="cursor: pointer;">
                                    <td>3</td>
                                    <td><span class="badge bg-secondary">HLP-003</span></td>
                                    <td>How to manage channels</td>
                                    <td>Channels</td>
                                </tr>
                                <tr onclick="openHelpDetail(4)" style="cursor: pointer;">
                                    <td>4</td>
                                    <td><span class="badge bg-secondary">HLP-004</span></td>
                                    <td>System user permissions</td>
                                    <td>Admin</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Help Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Help Details</h6>
                </div>
                <div class="card-body" id="helpDetail">
                    <p class="text-muted">Select a help topic from the table to view details here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpDetail(id) {
    let detailBox = document.getElementById('helpDetail');
    let content = "";

    if (id === 1) {
        content = `
            <h6>How to add a new client</h6>
            <p>Go to the Clients page and click the <b>Add Client</b> button. Fill in the client details like name, email, and phone, then click <b>Save</b>.</p>
        `;
    } else if (id === 2) {
        content = `
            <h6>How to assign packages</h6>
            <p>Navigate to the Client Packages page. Select a client, click <b>Edit</b>, then check the packages you want to assign. Finally, click <b>Save</b>.</p>
        `;
    } else if (id === 3) {
        content = `
            <h6>How to manage channels</h6>
            <p>Go to the Channels page. You can <b>Add</b>, <b>Edit</b>, or <b>View</b> channels. Use the search bar to quickly find a channel.</p>
        `;
    } else if (id === 4) {
        content = `
            <h6>System user permissions</h6>
            <p>Admins can manage permissions for users under the <b>Permissions</b> menu. Roles include Admin, Manager, and Client.</p>
        `;
    }

    detailBox.innerHTML = content;
}
</script>
@endsection
