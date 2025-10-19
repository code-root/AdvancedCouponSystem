@extends('dashboard.layouts.vertical', ['title' => 'Settings'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css'])
@endsection

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Configuration', 'title' => 'Settings'])

    <div class="row">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-header border-bottom border-dashed">
                    <div class="nav flex-wrap flex-lg-nowrap nav-pills" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical">
                        <a class="nav-link flex-grow-1 w-100 text-lg-center active show" id="v-pills-general-tab"
                            data-bs-toggle="pill" href="#v-pills-general" role="tab" aria-controls="v-pills-general"
                            aria-selected="true">
                            <i class="ti ti-home-2 fs-18 align-text-top d-inline-block me-1"></i>
                            <span>General</span>
                        </a>
                        <a class="nav-link flex-grow-1 w-100 text-lg-center" id="v-pills-email-tab"
                            data-bs-toggle="pill" href="#v-pills-email" role="tab"
                            aria-controls="v-pills-email" aria-selected="false">
                            <i class="ti ti-mail fs-18 align-text-top d-inline-block me-1"></i>
                            <span>Email Settings</span>
                        </a>
                        <a class="nav-link flex-grow-1 w-100 text-lg-center" id="v-pills-notifications-tab"
                            data-bs-toggle="pill" href="#v-pills-notifications" role="tab"
                            aria-controls="v-pills-notifications" aria-selected="false">
                            <i class="ti ti-bell fs-18 align-text-top d-inline-block me-1"></i>
                            <span>Notifications</span>
                        </a>
                        <a class="nav-link flex-grow-1 w-100 text-lg-center" id="v-pills-security-tab"
                            data-bs-toggle="pill" href="#v-pills-security" role="tab" aria-controls="v-pills-security"
                            aria-selected="false">
                            <i class="ti ti-shield fs-18 align-text-top d-inline-block me-1"></i>
                            <span>Security</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade active show" id="v-pills-general" role="tabpanel"
                            aria-labelledby="v-pills-general-tab">
                            <div class="bg-body px-3 py-2 rounded-2 mb-3">
                                <h4 class="fw-semibold mb-0"><i class="ti ti-settings align-baseline me-1"></i> General Settings</h4>
                            </div>
                            <form class="mb-4">
                                <div class="row gy-4">
                                    <div class="col-md-6">
                                        <label for="siteName" class="form-label">Site Name</label>
                                        <input type="text" id="siteName" class="form-control"
                                            value="AdvancedCouponSystem" placeholder="Enter Site Name">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="siteDescription" class="form-label">Site Description</label>
                                        <input type="text" id="siteDescription" class="form-control"
                                            value="Affiliate Marketing Platform" placeholder="Enter Description">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select" id="timezone">
                                            <option>UTC</option>
                                            <option>America/New_York</option>
                                            <option>Europe/London</option>
                                            <option>Asia/Dubai</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="currency" class="form-label">Default Currency</label>
                                        <select class="form-select" id="currency">
                                            <option>USD</option>
                                            <option>EUR</option>
                                            <option>GBP</option>
                                            <option>AED</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-soft-danger">Cancel</button>
                                <button type="button" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-email" role="tabpanel"
                            aria-labelledby="v-pills-email-tab">
                            <div class="bg-body px-3 py-2 rounded-2 mb-3">
                                <h4 class="fw-semibold mb-0"><i class="ti ti-mail align-baseline me-1"></i> Email Configuration</h4>
                            </div>
                            <form class="mb-4">
                                <div class="row gy-4">
                                    <div class="col-md-6">
                                        <label for="smtpHost" class="form-label">SMTP Host</label>
                                        <input type="text" id="smtpHost" class="form-control" placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="smtpPort" class="form-label">SMTP Port</label>
                                        <input type="number" id="smtpPort" class="form-control" placeholder="587">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="smtpUsername" class="form-label">Username</label>
                                        <input type="text" id="smtpUsername" class="form-control" placeholder="your-email@gmail.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="smtpPassword" class="form-label">Password</label>
                                        <input type="password" id="smtpPassword" class="form-control">
                                    </div>
                                </div>
                            </form>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-soft-danger">Cancel</button>
                                <button type="button" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-notifications" role="tabpanel"
                            aria-labelledby="v-pills-notifications-tab">
                            <div class="bg-body px-3 py-2 rounded-2 mb-3">
                                <h4 class="fw-semibold mb-0"><i class="ti ti-bell align-baseline me-1"></i> Notification Settings</h4>
                            </div>
                            <form class="mb-4">
                                <div class="row gy-4">
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                            <label class="form-check-label" for="emailNotif">Email Notifications</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="browserNotif" checked>
                                            <label class="form-check-label" for="browserNotif">Browser Notifications</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-soft-danger">Cancel</button>
                                <button type="button" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-security" role="tabpanel"
                            aria-labelledby="v-pills-security-tab">
                            <div class="bg-body px-3 py-2 rounded-2 mb-3">
                                <h4 class="fw-semibold mb-0"><i class="ti ti-shield align-baseline me-1"></i> Security Settings</h4>
                            </div>
                            <form class="mb-4">
                                <div class="row gy-4">
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="twoFactor">
                                            <label class="form-check-label" for="twoFactor">Two-Factor Authentication</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="sessionTimeout" checked>
                                            <label class="form-check-label" for="sessionTimeout">Auto Logout on Inactivity</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-soft-danger">Cancel</button>
                                <button type="button" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

