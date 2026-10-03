<x-layouts.portal title="Profile">
    <div class="max-w-2xl">
        @if (session('password_success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 flex items-center gap-2"><x-lucide name="CheckCircle" class="w-4 h-4"/> {{ session('password_success') }}</div>
        @endif

        {{-- Profile details --}}
        <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-8 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-5">Profile details</h3>
            <form method="POST" action="{{ route('portal.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')

                <div class="flex items-center gap-4">
                    @if ($user->avatar_url)
                        <img src="{{ media_url($user->avatar_url) }}" class="w-16 h-16 rounded-full object-cover" alt="">
                    @else
                        <span class="w-16 h-16 rounded-full bg-primary text-white grid place-items-center text-xl font-semibold">{{ $user->initials() }}</span>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Profile photo</label>
                        <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/20">
                        @error('avatar')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-muted-foreground font-normal">(sign-in — contact admin to change)</span></label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-lg border border-border bg-gray-50 px-3.5 py-2.5 text-sm text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Bio</label>
                    <textarea name="bio" rows="3" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="A little about you…">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-100"><x-lucide name="Save" class="w-4 h-4"/> Save changes</button>
            </form>
        </div>

        {{-- Change password --}}
        <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-5">Change password</h3>
            <form method="POST" action="{{ route('portal.profile.password') }}" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current password</label>
                    <input type="password" name="current_password" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('current_password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                        <input type="password" name="password" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"><x-lucide name="Lock" class="w-4 h-4"/> Update password</button>
            </form>
        </div>
    </div>
</x-layouts.portal>
