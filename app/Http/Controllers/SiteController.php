<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\CoreValue;
use App\Models\Cookie;
use App\Models\Course;
use App\Models\Faq;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\Service;
use App\Models\Stat;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function about()
    {
        return view('pages.about', [
            'milestones' => Milestone::orderBy('sort_order')->get(),
            'values'     => CoreValue::orderBy('sort_order')->get(),
            'stats'      => Stat::group('about')->get(),
        ]);
    }

    public function services()
    {
        return view('pages.services', [
            'services' => Service::active()->get(),
        ]);
    }

    public function pricing()
    {
        return view('pages.pricing', [
            'courses' => Course::active()->with('features')->get(),
            'faqs'    => Faq::published()->category('pricing')->get(),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'faqs' => Faq::published()->category('contact')->get(),
        ]);
    }

    public function consultation()
    {
        return view('pages.consultation');
    }

    public function verifyCertificate()
    {
        return view('pages.verify-certificate');
    }

    public function apply(Request $request)
    {
        return view('pages.apply', [
            'courses'      => Course::active()->get(),
            'selectedSlug' => $request->query('course'),
            'classFormat'  => $request->query('format', 'online'),
        ]);
    }

    public function legal(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('pages.legal', [
            'page'    => $page,
            'cookies' => $slug === 'cookies' ? Cookie::orderBy('sort_order')->get() : collect(),
        ]);
    }

    public function advertClick(Advert $advert)
    {
        $advert->increment('clicks');

        $url = $advert->link_url ?: '/';

        return redirect()->away(
            str_starts_with($url, 'http') ? $url : url($url)
        );
    }
}
