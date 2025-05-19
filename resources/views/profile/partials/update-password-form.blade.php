<section>
    <header>
        <h2 class="text-lg font-medium text-dark">
            تغییر رمز عبور
        </h2>
        <p class="mt-1 text-sm text-secondary">
            رمز عبور قوی و غیرقابل حدس انتخاب کنید.
        </p>
    </header>
    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="current_password" class="form-label">رمز عبور فعلی</label>
            <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
            @error('current_password', 'updatePassword')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">رمز عبور جدید</label>
            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" />
            @error('password', 'updatePassword')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">تکرار رمز عبور جدید</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-primary">ذخیره رمز عبور</button>
        @if (session('status') === 'password-updated')
            <span class="text-success ms-3">رمز عبور با موفقیت تغییر کرد.</span>
        @endif
    </form>
</section>
