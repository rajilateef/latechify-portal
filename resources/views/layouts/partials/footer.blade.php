@php
    $socials = array_filter([
        'instagram' => setting('instagram_url'),
        'facebook'  => setting('facebook_url'),
        'twitter'   => setting('twitter_url'),
        'linkedin'  => setting('linkedin_url'),
    ]);
@endphp

<footer class="bg-gray-900 text-white">
    <div class="container-custom py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            {{-- About --}}
            <div class="space-y-6">
                <img src="{{ media_url(setting('logo_white'), 'assets/imgs/latechify_logo_white.png') }}" alt="{{ setting('site_name') }}" loading="lazy" decoding="async" class="h-12">
                <p class="text-gray-400">{{ setting('footer_about') }}</p>
                <div class="flex gap-3">
                    @foreach ($socials as $network => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $network }}"
                           class="bg-white/10 hover:bg-primary p-2.5 rounded-full transition-colors">
                            <x-social-icon :network="$network" class="w-4 h-4"/>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h3 class="text-lg font-semibold mb-6">Quick Links</h3>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">About Us</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white transition-colors">Our Services</a></li>
                    <li><a href="{{ route('courses.index') }}" class="hover:text-white transition-colors">Courses</a></li>
                    <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">Pricing</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            {{-- Resources --}}
            <div>
                <h3 class="text-lg font-semibold mb-6">Resources</h3>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="{{ route('consultation') }}" class="hover:text-white transition-colors">Free Consultation</a></li>
                    <li><a href="{{ route('apply') }}" class="hover:text-white transition-colors">Apply Now</a></li>
                    <li><a href="{{ route('verify-certificate') }}" class="hover:text-white transition-colors">Verify Certificate</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookie Policy</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-lg font-semibold mb-6">Contact Info</h3>
                <ul class="space-y-4 text-gray-400">
                    <li class="flex items-start gap-3"><x-lucide name="MapPin" class="w-5 h-5 text-primary-0 mt-0.5 shrink-0"/><span>{{ setting('contact_address') }}</span></li>
                    <li class="flex items-center gap-3"><x-lucide name="PhoneCall" class="w-5 h-5 text-primary-0 shrink-0"/><span>{{ setting('contact_phone') }}{{ setting('contact_phone2') ? ', '.setting('contact_phone2') : '' }}</span></li>
                    <li class="flex items-center gap-3"><x-lucide name="Mail" class="w-5 h-5 text-primary-0 shrink-0"/><span>{{ setting('contact_email') }}</span></li>
                    <li class="pt-2">
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-primary-0 hover:bg-primary-0/90 text-white px-5 py-2.5 rounded-lg transition-colors">
                            Contact Us <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-gray-950">
        <div class="container-custom py-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ setting('site_name', 'Latechify Digital Hub') }}. All rights reserved.</p>
            <div class="flex gap-6 text-sm">
                <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white">Terms</a>
                <a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white">Privacy</a>
                <a href="{{ route('cookies') }}" class="text-gray-400 hover:text-white">Cookies</a>
            </div>
        </div>
    </div>
</footer>
