<!-- Customizer -->
<div class="customizer-setting d-none d-md-block">
    <a href="#" class="customizer-toggle d-flex align-items-center justify-content-center">
        <i class="ti ti-settings fs-22"></i>
    </a>
</div>

<!-- Customizer Panel -->
<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
    <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
        <h5 class="m-0 me-2 text-white">Theme Customizer</h5>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div data-simplebar class="h-100">
            <div class="p-4">
                <!-- Layout -->
                <h6 class="fw-semibold fs-15 mb-3">Layout</h6>
                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-layout" id="layout-vertical" value="vertical">
                            <label class="form-check-label p-0 avatar-md w-100" for="layout-vertical">
                                <span class="d-flex gap-1 h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                            <span class="d-block p-1 bg-primary-subtle rounded mb-1"></span>
                                            <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                            <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                            <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                            <span class="bg-light d-block p-1 mt-auto"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Vertical</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-layout" id="layout-horizontal" value="horizontal">
                            <label class="form-check-label p-0 avatar-md w-100" for="layout-horizontal">
                                <span class="d-flex h-100 flex-column gap-1">
                                    <span class="bg-light d-flex p-1 gap-1 align-items-center">
                                        <span class="d-block p-1 bg-primary-subtle rounded me-1"></span>
                                        <span class="d-block p-1 pb-0 bg-primary-subtle ms-auto"></span>
                                        <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                    </span>
                                    <span class="bg-light d-block p-1"></span>
                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Horizontal</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-layout" id="layout-two-column" value="twocolumn">
                            <label class="form-check-label p-0 avatar-md w-100" for="layout-two-column">
                                <span class="d-flex gap-1 h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 flex-column gap-1">
                                            <span class="d-block p-1 bg-primary-subtle mb-1"></span>
                                            <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                            <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                            <span class="d-block p-1 pb-0 bg-primary-subtle"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Two Column</h5>
                    </div>
                </div>

                <!-- Color Scheme -->
                <h6 class="fw-semibold fs-15 mb-3">Color Scheme</h6>
                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-theme" id="layout-color-light" value="light">
                            <label class="form-check-label p-0 avatar-xs" for="layout-color-light">
                                <span class="avatar-title rounded bg-light border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Light</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-theme" id="layout-color-dark" value="dark">
                            <label class="form-check-label p-0 avatar-xs" for="layout-color-dark">
                                <span class="avatar-title rounded bg-dark border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Dark</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-theme" id="layout-color-auto" value="auto">
                            <label class="form-check-label p-0 avatar-xs" for="layout-color-auto">
                                <span class="avatar-title rounded bg-primary border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Auto</h5>
                    </div>
                </div>

                <!-- Sidebar -->
                <h6 class="fw-semibold fs-15 mb-3">Sidebar</h6>
                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-light" value="light">
                            <label class="form-check-label p-0 avatar-xs" for="sidebar-color-light">
                                <span class="avatar-title rounded bg-light border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Light</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-dark" value="dark">
                            <label class="form-check-label p-0 avatar-xs" for="sidebar-color-dark">
                                <span class="avatar-title rounded bg-dark border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Dark</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-brand" value="brand">
                            <label class="form-check-label p-0 avatar-xs" for="sidebar-color-brand">
                                <span class="avatar-title rounded bg-primary border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Brand</h5>
                    </div>
                </div>

                <!-- Topbar -->
                <h6 class="fw-semibold fs-15 mb-3">Topbar</h6>
                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-light" value="light">
                            <label class="form-check-label p-0 avatar-xs" for="topbar-color-light">
                                <span class="avatar-title rounded bg-light border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Light</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-dark" value="dark">
                            <label class="form-check-label p-0 avatar-xs" for="topbar-color-dark">
                                <span class="avatar-title rounded bg-dark border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Dark</h5>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-brand" value="brand">
                            <label class="form-check-label p-0 avatar-xs" for="topbar-color-brand">
                                <span class="avatar-title rounded bg-primary border"></span>
                            </label>
                        </div>
                        <h5 class="fs-13 text-center mt-2">Brand</h5>
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="d-grid mt-4">
                    <button class="btn btn-primary" id="reset-layout">Reset to Default</button>
                </div>
            </div>
        </div>
    </div>
</div>