<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General / Branding ──
            'general' => [
                'site_name'        => 'Latechify Digital Hub',
                'site_tagline'     => 'Your Tech Training & Digital Solutions Partner',
                'logo'             => 'assets/imgs/latechify_logo.png',
                'logo_white'       => 'assets/imgs/latechify_logo_white.png',
                'brand_badge_title'=> 'Latechify',
                'brand_badge_sub'  => 'Digital Hub',
                'footer_about'     => 'Empowering individuals and businesses with cutting-edge digital skills and solutions for the modern world.',
                'cta_label'        => 'Enrol Today 👍',
            ],
            // ── Contact ──
            'contact' => [
                'contact_address'   => "Tam 5, Area 5, Preboye's World, Opp UI Shopping Complex, Ibadan",
                'topbar_address'    => 'No 3, Monremi street, Are Bodija, Ibadan',
                'contact_phone'     => '08064539275',
                'contact_phone2'    => '08175412933',
                'contact_email'     => 'latechifydh@gmail.com',
                'whatsapp_number'   => '2348064539275',
                'notification_email'=> 'edutal26@gmail.com',
                'business_hours'    => "Monday - Friday: 9:00 AM - 5:00 PM|Saturday: 12:00 PM - 5:00 PM|Sunday: Closed",
                'map_embed_url'     => 'https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d31650.449093879663!2d3.8969833222289862!3d7.431335832084319!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sng!4v1747673316883!5m2!1sen!2sng',
                'directions_url'    => 'https://maps.google.com/maps?daddr=7.437187,3.915774',
            ],
            // ── Social ──
            'social' => [
                'instagram_url' => 'https://www.instagram.com/latechify_digital_hub?igsh=MWxlcXh2a2RhMmJibg==',
                'facebook_url'  => 'https://facebook.com/latechify',
                'twitter_url'   => 'https://twitter.com/latechify',
                'linkedin_url'  => 'https://linkedin.com/company/latechify',
            ],
            // ── Payment ──
            'payment' => [
                'currency'            => 'NGN',
                'paystack_public_key' => '',
                'paystack_secret_key' => '',
                'monnify_api_key'       => '',
                'monnify_secret_key'    => '',
                'monnify_contract_code' => '',
                'monnify_live'          => '0',
                'bank_name'           => 'Opay',
                'bank_account_name'   => 'Raji Ajibola Lateef',
                'bank_account_number' => '8175412933',
                'enterprise_base_price'        => '500000',
                'enterprise_price_per_student' => '200000',
            ],
            // ── Summer Coding Camp ──
            'camp' => [
                'camp_title'      => 'Summer Coding Camp 2025',
                'camp_subtitle'   => 'A hands-on holiday coding experience where young learners build real projects, make friends, and discover the joy of technology — guided by expert mentors.',
                'camp_badge'      => 'Registration Open',
                'camp_dates'      => 'Aug 4 – Aug 29, 2025',
                'camp_duration'   => '4 weeks · Mon–Fri, 10am–2pm',
                'camp_location'    => 'Latechify Hub, Bodija, Ibadan',
                'camp_fee_physical' => '70000',
                'camp_fee_virtual'  => '50000',
                'camp_image'      => 'assets/imgs/session2.jpg',
                'camp_highlights' => "Beginner-friendly, project-based curriculum\nExpert mentors & small class sizes\nBuild & launch a real capstone project\nCertificate of completion\nFun demo day for parents\nSnacks & camp T-shirt included",
                'camp_tracks'     => "Scratch & Game Development\nWeb Development (HTML, CSS, JS)\nMobile App Development\nPython & Data\nRobotics & AI Basics",
            ],
            // ── Home section copy ──
            'home' => [
                'about_heading'    => 'About Latechify',
                'about_body_1'     => 'Latechify Digital Hub is a leading technology training center and digital solutions provider dedicated to transforming careers and businesses through innovative tech education and services.',
                'about_body_2'     => 'Founded by industry experts with years of experience, we offer comprehensive training programs in web development, mobile app development, data science, and more. Our custom digital solutions help businesses leverage the latest technologies to grow and thrive in today\'s digital landscape.',
                'about_image'      => 'assets/imgs/banner.jpg',
                'services_heading' => 'Our Services',
                'services_sub'     => 'From expert-led training programs to custom digital solutions, we provide comprehensive tech services to meet your needs.',
                'cohort_name'      => 'Current Cohort: Summer 2025',
                'cohort_status'    => 'Applications open for Fall 2025',
                'cohort_image'     => 'assets/imgs/cohort.jpg',
                'hero_background'   => 'background/hero_bg.jpg',
                'testimonial_card_heading' => 'Transforming Diverse Backgrounds Into Tech Careers',
                'promo_heading'    => 'Latest Promotions',
                'promo_sub'        => 'Special offers, events and announcements from Latechify Digital Hub.',
            ],
            // ── Home-page section headings ──
            'sections' => [
                'services_eyebrow'     => 'Services',
                'services_statement'   => 'We provide end-to-end solutions to help your business launch, grow, and scale with confidence.',
                'benefits_eyebrow'     => 'Why Latechify',
                'benefits_heading'     => 'Why Learners Choose Us',
                'benefits_sub'         => 'Everything you need to go from curious beginner to hired tech professional — under one roof.',
                'tech_eyebrow'         => 'Tech Stack',
                'tech_heading'         => "Technologies & Tools You'll Master",
                'tech_sub'             => 'The industry-standard tools our learners build with every day.',
                'testimonials_eyebrow' => 'Testimonials',
                'testimonials_heading' => 'What Our Clients Say',
                'testimonials_sub'     => "Don't just take our word for it. Here's what our students and clients have to say about their experience with Latechify Digital Hub.",
                'blog_eyebrow'         => 'Blog',
                'blog_heading'         => 'Latest from our Blog',
                'blog_sub'             => 'Insights, tutorials, and stories from our team to help you learn, build, and grow in tech.',
                'cohort_heading'       => 'Current Cohort Experience',
                'cohort_sub'           => 'Join our vibrant learning community and experience the interactive tech training environment firsthand',
                'faq_heading'          => 'Frequently Asked Questions',
                'faq_sub'              => 'Find answers to the most common questions about our courses and services',
                'partners_eyebrow'     => 'Recognition',
                'partners_heading'     => 'Accreditations & Partnerships',
                'partners_sub'         => 'Proudly recognised and supported by leading institutions.',
                'contact_cta_heading'  => 'Ready to Start Your Tech Journey?',
                'contact_cta_sub'      => "Join our community of learners and professionals. Enrol in a course, request a custom digital solution, or simply reach out — we're here to help.",
            ],
            // ── SEO ──
            'seo' => [
                'meta_title'       => 'Latechify Digital Hub - Your Tech Training & Digital Solutions Partner',
                'meta_description' => 'Latechify Digital Hub offers comprehensive tech training, web development, mobile app development and digital marketing solutions.',
            ],
        ];

        foreach ($settings as $group => $pairs) {
            foreach ($pairs as $key => $value) {
                Setting::updateOrCreate(['key' => $key], [
                    'value' => $value,
                    'group' => $group,
                    'type'  => 'text',
                ]);
            }
        }
    }
}
