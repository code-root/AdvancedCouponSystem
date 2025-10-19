@extends('admin.layouts.ajax-wrapper')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">SEO Settings</h4>
                <p class="text-muted mb-0">Configure search engine optimization and social media settings</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Meta Tags & SEO</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.seo.save') }}">
                    @csrf
                    
                    <!-- Meta Description -->
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                  id="meta_description" name="meta_description" rows="3" 
                                  maxlength="160" placeholder="Brief description of your site (max 160 characters)">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                        <div class="form-text">
                            <span id="meta_desc_count">0</span>/160 characters
                        </div>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Meta Keywords -->
                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                               id="meta_keywords" name="meta_keywords" 
                               value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}"
                               placeholder="keyword1, keyword2, keyword3">
                        <div class="form-text">Separate keywords with commas</div>
                        @error('meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Meta Author -->
                    <div class="mb-3">
                        <label for="meta_author" class="form-label">Meta Author</label>
                        <input type="text" class="form-control @error('meta_author') is-invalid @enderror" 
                               id="meta_author" name="meta_author" 
                               value="{{ old('meta_author', $settings['meta_author'] ?? '') }}">
                        @error('meta_author')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Robots Meta -->
                    <div class="mb-3">
                        <label for="robots_meta" class="form-label">Robots Meta</label>
                        <select class="form-select @error('robots_meta') is-invalid @enderror" 
                                id="robots_meta" name="robots_meta">
                            <option value="index,follow" {{ old('robots_meta', $settings['robots_meta'] ?? 'index,follow') == 'index,follow' ? 'selected' : '' }}>Index, Follow</option>
                            <option value="index,nofollow" {{ old('robots_meta', $settings['robots_meta'] ?? '') == 'index,nofollow' ? 'selected' : '' }}>Index, No Follow</option>
                            <option value="noindex,follow" {{ old('robots_meta', $settings['robots_meta'] ?? '') == 'noindex,follow' ? 'selected' : '' }}>No Index, Follow</option>
                            <option value="noindex,nofollow" {{ old('robots_meta', $settings['robots_meta'] ?? '') == 'noindex,nofollow' ? 'selected' : '' }}>No Index, No Follow</option>
                        </select>
                        @error('robots_meta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Social Media & Analytics</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.seo.save') }}">
                    @csrf
                    
                    <!-- Open Graph Title -->
                    <div class="mb-3">
                        <label for="og_title" class="form-label">Open Graph Title</label>
                        <input type="text" class="form-control @error('og_title') is-invalid @enderror" 
                               id="og_title" name="og_title" 
                               value="{{ old('og_title', $settings['og_title'] ?? '') }}"
                               maxlength="60">
                        <div class="form-text">Title for social media sharing (max 60 characters)</div>
                        @error('og_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Open Graph Description -->
                    <div class="mb-3">
                        <label for="og_description" class="form-label">Open Graph Description</label>
                        <textarea class="form-control @error('og_description') is-invalid @enderror" 
                                  id="og_description" name="og_description" rows="3" 
                                  maxlength="160">{{ old('og_description', $settings['og_description'] ?? '') }}</textarea>
                        <div class="form-text">Description for social media sharing (max 160 characters)</div>
                        @error('og_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Social Media Links -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" 
                                       id="facebook_url" name="facebook_url" 
                                       value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}"
                                       placeholder="https://facebook.com/yourpage">
                                @error('facebook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control @error('twitter_url') is-invalid @enderror" 
                                       id="twitter_url" name="twitter_url" 
                                       value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}"
                                       placeholder="https://twitter.com/yourhandle">
                                @error('twitter_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" 
                                       id="linkedin_url" name="linkedin_url" 
                                       value="{{ old('linkedin_url', $settings['linkedin_url'] ?? '') }}"
                                       placeholder="https://linkedin.com/company/yourcompany">
                                @error('linkedin_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" 
                                       id="instagram_url" name="instagram_url" 
                                       value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}"
                                       placeholder="https://instagram.com/yourhandle">
                                @error('instagram_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Analytics -->
                    <div class="mb-3">
                        <label for="google_analytics_id" class="form-label">Google Analytics ID</label>
                        <input type="text" class="form-control @error('google_analytics_id') is-invalid @enderror" 
                               id="google_analytics_id" name="google_analytics_id" 
                               value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}"
                               placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X">
                        <div class="form-text">Google Analytics tracking ID</div>
                        @error('google_analytics_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="google_tag_manager_id" class="form-label">Google Tag Manager ID</label>
                        <input type="text" class="form-control @error('google_tag_manager_id') is-invalid @enderror" 
                               id="google_tag_manager_id" name="google_tag_manager_id" 
                               value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id'] ?? '') }}"
                               placeholder="GTM-XXXXXXX">
                        <div class="form-text">Google Tag Manager container ID</div>
                        @error('google_tag_manager_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="facebook_pixel_id" class="form-label">Facebook Pixel ID</label>
                        <input type="text" class="form-control @error('facebook_pixel_id') is-invalid @enderror" 
                               id="facebook_pixel_id" name="facebook_pixel_id" 
                               value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}"
                               placeholder="123456789012345">
                        <div class="form-text">Facebook Pixel tracking ID</div>
                        @error('facebook_pixel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Save SEO Settings
                        </button>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">SEO Guidelines</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Meta Tags Best Practices</h6>
                    <ul class="mb-0">
                        <li>Meta description: 150-160 characters</li>
                        <li>Meta title: 50-60 characters</li>
                        <li>Use relevant keywords naturally</li>
                        <li>Make descriptions compelling</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Social Media</h6>
                    <ul class="mb-0">
                        <li>Open Graph tags improve sharing</li>
                        <li>Use high-quality images for OG</li>
                        <li>Keep social URLs updated</li>
                        <li>Test sharing on platforms</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <h6 class="alert-heading">Analytics</h6>
                    <ul class="mb-0">
                        <li>Google Analytics tracks visitors</li>
                        <li>Tag Manager manages all tags</li>
                        <li>Facebook Pixel tracks conversions</li>
                        <li>Verify tracking is working</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for meta description
    const metaDesc = document.getElementById('meta_description');
    const metaDescCount = document.getElementById('meta_desc_count');
    
    function updateCharCount() {
        const count = metaDesc.value.length;
        metaDescCount.textContent = count;
        
        if (count > 160) {
            metaDescCount.parentElement.classList.add('text-danger');
        } else if (count > 150) {
            metaDescCount.parentElement.classList.add('text-warning');
        } else {
            metaDescCount.parentElement.classList.remove('text-danger', 'text-warning');
        }
    }
    
    metaDesc.addEventListener('input', updateCharCount);
    updateCharCount(); // Initial count
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // URL validation
    const urlInputs = document.querySelectorAll('input[type="url"]');
    urlInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !this.value.match(/^https?:\/\/.+/)) {
                this.setCustomValidity('Please enter a valid URL starting with http:// or https://');
            } else {
                this.setCustomValidity('');
            }
        });
    });
});
</script>
@endpush
