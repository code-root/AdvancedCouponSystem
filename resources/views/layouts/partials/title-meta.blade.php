<meta charset="utf-8" />
<title>{{ $title ?? 'Welcome' }} | {{ \App\Services\SiteSettingService::getSiteName() }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta content="{{ \App\Services\SiteSettingService::getMetaDescription() }}" name="description" />
<meta content="{{ \App\Services\SiteSettingService::getMetaAuthor() }}" name="author" />
<link rel="shortcut icon" href="{{ \App\Services\SiteSettingService::getFavicon() }}">



