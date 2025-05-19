<section>
    <header>
        <h2 class="text-lg font-medium text-dark">
            تغییر تصویر پروفایل
        </h2>
        <p class="mt-1 text-sm text-secondary">
            تصویر جدید بارگذاری کنید.
        </p>
    </header>
    <form method="post" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="mt-4">
        @csrf
        <div class="mb-3">
            <input type="file" name="avatar" class="form-control" accept="image/*" required>
            @error('avatar')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-info">ذخیره تصویر</button>
        @if (session('status') === 'avatar-updated')
            <span class="text-success ms-3">تصویر با موفقیت تغییر کرد.</span>
        @endif
    </form>
</section>
