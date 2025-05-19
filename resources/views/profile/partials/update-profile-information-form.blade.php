<section>
    <header>
        <h2 class="text-lg font-medium text-dark">
            اطلاعات کاربری
        </h2>
        <p class="mt-1 text-sm text-secondary">
            اطلاعات حساب خود را ویرایش کنید.
        </p>
    </header>
    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">نام و نام خانوادگی</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">ایمیل</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-success">ذخیره اطلاعات</button>
        @if (session('status') === 'profile-updated')
            <span class="text-success ms-3">اطلاعات با موفقیت ذخیره شد.</span>
        @endif
    </form>
</section>
