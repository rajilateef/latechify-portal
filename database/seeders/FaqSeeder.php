<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $home = [
            ['What courses do you offer?', 'We offer a variety of tech courses including Web Development, Mobile App Development, Data Science, UI/UX Design, and specialized programming languages like JavaScript, Python, and more.'],
            ['Do I need prior coding experience to join your courses?', 'Not for our beginner courses! We have programs designed for complete beginners as well as more advanced courses for those with prior experience. Each course listing specifies the prerequisites.'],
            ['How long are your training programs?', 'Our courses range from 6 to 16 weeks depending on the subject matter and depth. We also offer shorter workshop formats and self-paced options.'],
            ['Are your courses online or in-person?', 'We offer both online and in-person options for most of our courses. Our online courses include live sessions and interactive components to ensure an engaging learning experience.'],
            ['What kind of digital services do you provide for businesses?', 'We provide custom web and mobile application development, UI/UX design, digital transformation consulting, e-commerce solutions, and technology strategy services tailored to your business needs.'],
            ['Do you offer job placement assistance after completing a course?', 'Yes, we offer career support including resume reviews, portfolio guidance, interview preparation, and connections with our hiring partners. Many of our graduates have successfully transitioned to tech careers.'],
        ];

        $pricing = [
            ["What's the difference between Physical and Online learning?", "Physical learning takes place in our campus facilities with in-person instruction. Online learning offers the same curriculum but delivered remotely through our virtual classroom platform. Online courses are discounted as they don't include physical classroom costs."],
            ['Are there any hidden fees?', "No, the prices listed are all-inclusive. There are no hidden fees or additional charges. Once you select a plan, you'll only pay what's displayed."],
            ['Do you offer refunds?', "We offer a 7-day money-back guarantee if you're not satisfied with our services. Contact our support team within 7 days of purchase for a full refund."],
            ['What payment methods do you accept?', 'We accept credit/debit cards, bank transfers, and mobile money payments. All payments are processed securely through our payment partners.'],
        ];

        $contact = [
            ['What are your business hours?', 'Our office is open from Monday to Friday, 9:00 AM to 5:00 PM, and Saturday from 12:00 PM to 5:00 PM. We are closed on Sundays and public holidays.'],
            ['How quickly do you respond to inquiries?', 'We aim to respond to all inquiries within 24 business hours. For urgent matters, we recommend calling our office directly.'],
            ['Do you offer virtual consultations?', 'Yes, we offer virtual consultations via Zoom, Google Meet, or Microsoft Teams for clients who cannot visit our office in person.'],
            ['Is there parking available at your office?', 'Yes, we have ample parking space available for our clients and visitors at our office location.'],
        ];

        foreach (['home' => $home, 'pricing' => $pricing, 'contact' => $contact] as $category => $faqs) {
            foreach ($faqs as $i => [$q, $a]) {
                Faq::create([
                    'question'     => $q,
                    'answer'       => $a,
                    'category'     => $category,
                    'sort_order'   => $i + 1,
                    'is_published' => true,
                ]);
            }
        }
    }
}
