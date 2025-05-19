@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">

        <div class="col-md-4 mb-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <img src="{{ Auth::user()->profile_photo_url ?? asset('img/user.png') }}"
                         class="rounded-circle mb-3 border" width="100" height="100" alt="User Image">
                    <h5 class="card-title mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted mb-2">{{ Auth::user()->email }}</p>
                    <span class="badge bg-light text-dark mb-2">پروفایل کاربری</span>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- تب‌بندی -->
                    <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-info-tab" data-bs-toggle="tab" data-bs-target="#profile-info" type="button" role="tab" aria-controls="profile-info" aria-selected="true">
                                اطلاعات کاربری
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-tab-pane" type="button" role="tab" aria-controls="password-tab-pane" aria-selected="false">
                                تغییر رمز عبور
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="avatar-tab" data-bs-toggle="tab" data-bs-target="#avatar-tab-pane" type="button" role="tab" aria-controls="avatar-tab-pane" aria-selected="false">
                                تصویر کاربری
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-danger" id="delete-tab" data-bs-toggle="tab" data-bs-target="#delete-tab-pane" type="button" role="tab" aria-controls="delete-tab-pane" aria-selected="false">
                                حذف حساب
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="profileTabContent">
                        <!-- اطلاعات کاربری -->
                        <div class="tab-pane fade show active" id="profile-info" role="tabpanel" aria-labelledby="profile-info-tab">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                        <!-- تغییر رمز عبور -->
                        <div class="tab-pane fade" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab">
                            @include('profile.partials.update-password-form')
                        </div>
                        <!-- تغییر تصویر کاربری -->
                        <div class="tab-pane fade" id="avatar-tab-pane" role="tabpanel" aria-labelledby="avatar-tab">
                            @include('profile.partials.update-avatar-form')
                        </div>
                        <!-- حذف حساب -->
                        <div class="tab-pane fade" id="delete-tab-pane" role="tabpanel" aria-labelledby="delete-tab">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- بوت‌استرپ تب‌ها -->
@push('scripts')
<script>
    // فعال کردن تب بر اساس هش آدرس
    $(document).ready(function(){
        var hash = window.location.hash;
        if(hash){
            $('.nav-tabs button[data-bs-target="' + hash + '"]').tab('show');
        }
        $('.nav-tabs button').on('shown.bs.tab', function (e) {
            window.location.hash = $(e.target).data("bs-target");
        });
    });
</script>
@endpush
@endsection
