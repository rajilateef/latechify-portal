<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Benefit;
use App\Models\BlogPost;
use App\Models\CohortActivity;
use App\Models\Course;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\Partner;
use App\Models\Service;
use App\Models\Stat;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'slides'          => HeroSlide::active()->get(),
            'sectionAdverts'  => Advert::live()->forSection()->get(),
            'popupAdvert'     => Advert::live()->forPopup()->first(),
            'services'        => Service::active()->get(),
            'courses'         => Course::active()->get(),
            'benefits'        => Benefit::active()->get(),
            'impactStats'     => Stat::group('about')->get(),
            'technologies'    => Partner::active()->category('partner')->get(),
            'accreditations'  => Partner::active()->category('accreditation')->get(),
            'employers'       => Partner::active()->category('employer')->get(),
            'cohortStats'     => Stat::group('cohort')->get(),
            'cohortActivities'=> CohortActivity::orderBy('sort_order')->get(),
            'testimonials'    => Testimonial::published()->get(),
            'testimonialStats'=> Stat::group('testimonial')->get(),
            'faqs'            => Faq::published()->category('home')->get(),
            'blogPosts'       => BlogPost::published()->take(3)->get(),
        ]);
    }
}
