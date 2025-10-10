<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">{{ $title ?? 'Page Title' }}</h4>
    </div>

    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            @if(isset($subtitle) && $subtitle)
                <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $subtitle }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $title ?? 'Page' }}</li>
        </ol>
    </div>
</div>

