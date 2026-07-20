<x-layouts.app>
    @if ($popupAdvert)
        <x-slot:popup>
            @include('layouts.partials.advert-popup', ['advert' => $popupAdvert])
        </x-slot:popup>
    @endif

    @include('sections.hero')
    @include('sections.technologies')
    @include('sections.promotions')
    @include('sections.about')
    @include('sections.benefits')
    @include('sections.services')
    @include('sections.blog')
    @include('sections.cohort')
    @include('sections.testimonials')
    @include('sections.partners')
    @include('sections.faq')
    @include('sections.contact-cta')
</x-layouts.app>
