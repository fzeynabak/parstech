<section>
    <header>
        <h2 class="text-lg font-medium text-danger">
            حذف حساب کاربری
        </h2>
        <p class="mt-1 text-sm text-secondary">
            با حذف حساب، تمام اطلاعات شما پاک می‌شود. لطفاً رمز عبور را وارد کنید.
        </p>
    </header>
    <form method="post" action="{{ route('profile.destroy') }}" class="mt-4">
        @csrf
        @method('delete')
        <div class="mb-3">
            <label for="delete_password" class="form-label">رمز عبور</label>
            <input id="delete_password" name="password" type="password" class="form-control" required>
            @error('password', 'userDeletion')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button class="btn btn-outline-danger">حذف حساب</button>
    </form>
</section>
