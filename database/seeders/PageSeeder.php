<?php

namespace Database\Seeders;

use App\Models\Cookie;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lastUpdated = '2025-04-29';

        $termsBody = <<<'HTML'
<h2>1. Introduction</h2>
<p>Welcome to Latechify Digital Hub. These Terms of Service govern your use of our website, services, and products. By accessing or using our services, you agree to be bound by these Terms.</p>
<h2>2. User Accounts</h2>
<p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account.</p>
<p>You are responsible for safeguarding the password that you use to access the service and for any activities or actions under your password.</p>
<h2>3. Intellectual Property</h2>
<p>The Service and its original content, features, and functionality are and will remain the exclusive property of Latechify Digital Hub and its licensors. The Service is protected by copyright, trademark, and other laws.</p>
<h2>4. Links To Other Web Sites</h2>
<p>Our Service may contain links to third-party web sites or services that are not owned or controlled by Latechify Digital Hub.</p>
<p>Latechify Digital Hub has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party web sites or services. You further acknowledge and agree that Latechify Digital Hub shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such content, goods or services available on or through any such web sites or services.</p>
<h2>5. Termination</h2>
<p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
<p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service.</p>
<h2>6. Limitation Of Liability</h2>
<p>In no event shall Latechify Digital Hub, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to access or use the Service.</p>
<h2>7. Changes</h2>
<p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
<h2>8. Contact Us</h2>
<p>If you have any questions about these Terms, please contact us at legal@latechify.com.</p>
HTML;

        $privacyBody = <<<'HTML'
<h2>1. Introduction</h2>
<p>At Latechify Digital Hub, we respect your privacy and are committed to protecting your personal data. This privacy policy will inform you about how we look after your personal data when you visit our website and tell you about your privacy rights and how the law protects you.</p>
<h2>2. Data We Collect</h2>
<p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
<ul>
<li><strong>Identity Data</strong> includes first name, last name, username or similar identifier.</li>
<li><strong>Contact Data</strong> includes billing address, delivery address, email address and telephone numbers.</li>
<li><strong>Technical Data</strong> includes internet protocol (IP) address, browser type and version, time zone setting and location, browser plug-in types and versions, operating system and platform, and other technology on the devices you use to access this website.</li>
<li><strong>Usage Data</strong> includes information about how you use our website, products, and services.</li>
</ul>
<h2>3. How We Use Your Data</h2>
<p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
<ul>
<li>Where we need to perform the contract we are about to enter into or have entered into with you.</li>
<li>Where it is necessary for our legitimate interests and your interests and fundamental rights do not override those interests.</li>
<li>Where we need to comply with a legal obligation.</li>
</ul>
<h2>4. Data Security</h2>
<p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorized way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.</p>
<h2>5. Data Retention</h2>
<p>We will only retain your personal data for as long as reasonably necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, regulatory, tax, accounting or reporting requirements.</p>
<h2>6. Your Legal Rights</h2>
<p>Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to:</p>
<ul>
<li>Request access to your personal data.</li>
<li>Request correction of your personal data.</li>
<li>Request erasure of your personal data.</li>
<li>Object to processing of your personal data.</li>
<li>Request restriction of processing your personal data.</li>
<li>Request transfer of your personal data.</li>
<li>Right to withdraw consent.</li>
</ul>
<h2>7. Contact Us</h2>
<p>If you have any questions about this privacy policy or our privacy practices, please contact our data privacy manager at privacy@latechify.com.</p>
HTML;

        $cookiesBody = <<<'HTML'
<h2>1. What Are Cookies</h2>
<p>Cookies are small text files that are placed on your computer or mobile device when you browse websites. They are widely used in order to make websites work, or work more efficiently, as well as to provide information to the owners of the site.</p>
<h2>2. How We Use Cookies</h2>
<p>We use cookies for a variety of reasons detailed below:</p>
<ul>
<li><strong>Necessary/Essential Cookies</strong> - These cookies are essential to provide you with services available through our website and to enable you to use some of its features. Without these cookies, the services that you have asked for cannot be provided.</li>
<li><strong>Analytics Cookies</strong> - These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.</li>
<li><strong>Functionality Cookies</strong> - These cookies allow our website to remember choices you make (such as your user name, language or the region you are in) and provide enhanced, more personal features.</li>
<li><strong>Targeting Cookies</strong> - These cookies may be set through our site by our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant adverts on other sites.</li>
</ul>
<h2>3. Types of Cookies We Use</h2>
<p>The following table lists the specific cookies we use, along with their purpose and how long they remain on your device.</p>
<h2>4. How to Control Cookies</h2>
<p>You can control and/or delete cookies as you wish. You can delete all cookies that are already on your computer and you can set most browsers to prevent them from being placed. If you do this, however, you may have to manually adjust some preferences every time you visit a site and some services and functionalities may not work.</p>
<p>Most web browsers allow some control of most cookies through the browser settings. To find out more about cookies, including how to see what cookies have been set, visit www.aboutcookies.org or www.allaboutcookies.org.</p>
<h2>5. Contact Us</h2>
<p>If you have any questions about our cookie policy, please contact us at cookies@latechify.com.</p>
HTML;

        Page::updateOrCreate(
            ['slug' => 'terms'],
            [
                'title' => 'Terms of Service',
                'last_updated' => $lastUpdated,
                'body' => $termsBody,
                'meta_title' => 'Terms of Service | Latechify Digital Hub',
                'meta_description' => 'Read the Terms of Service governing your use of the Latechify Digital Hub website, services, and products.',
                'is_published' => true,
            ]
        );

        Page::updateOrCreate(
            ['slug' => 'privacy'],
            [
                'title' => 'Privacy Policy',
                'last_updated' => $lastUpdated,
                'body' => $privacyBody,
                'meta_title' => 'Privacy Policy | Latechify Digital Hub',
                'meta_description' => 'Learn how Latechify Digital Hub collects, uses, and protects your personal data, and understand your privacy rights.',
                'is_published' => true,
            ]
        );

        Page::updateOrCreate(
            ['slug' => 'cookies'],
            [
                'title' => 'Cookie Policy',
                'last_updated' => $lastUpdated,
                'body' => $cookiesBody,
                'meta_title' => 'Cookie Policy | Latechify Digital Hub',
                'meta_description' => 'Understand how Latechify Digital Hub uses cookies, the types of cookies we use, and how you can control them.',
                'is_published' => true,
            ]
        );

        $cookies = [
            ['name' => 'sessionid', 'purpose' => 'Maintains user session state across page requests.', 'duration' => '2 weeks', 'sort_order' => 1],
            ['name' => '_ga', 'purpose' => 'Used to distinguish users for Google Analytics.', 'duration' => '2 years', 'sort_order' => 2],
            ['name' => '_gid', 'purpose' => 'Used to distinguish users for Google Analytics.', 'duration' => '24 hours', 'sort_order' => 3],
        ];

        foreach ($cookies as $cookie) {
            Cookie::updateOrCreate(
                ['name' => $cookie['name']],
                [
                    'purpose' => $cookie['purpose'],
                    'duration' => $cookie['duration'],
                    'sort_order' => $cookie['sort_order'],
                ]
            );
        }
    }
}
