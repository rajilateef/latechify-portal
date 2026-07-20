<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'attributes' => [
                    'slug' => 'frontend-web-development',
                    'title' => 'Frontend Web Development',
                    'subtitle' => 'Master modern web development with HTML, CSS, JavaScript and React',
                    'description' => 'Master HTML, CSS, JavaScript, React and responsive design principles to build modern web interfaces.',
                    'long_description' => 'This comprehensive course takes you from the fundamentals of web development to building advanced, responsive user interfaces with React. You\'ll learn how to create visually appealing, interactive websites that provide excellent user experiences across all devices.',
                    'icon' => 'Layout',
                    'level' => 'Beginner to Advanced',
                    'duration' => '12 weeks',
                    'schedule' => 'Mon, Tues & Thurs, 10:00-1:00 PM, 2-5pm',
                    'start_date' => 'June 15, 2025',
                    'price_physical' => 300000,
                    'price_online' => 250000,
                    'rating' => 4.9,
                    'popular' => true,
                    'featured' => true,
                    'popular_feature' => 'Perfect for beginners',
                    'tags' => ['web', 'frontend', 'react'],
                    'image' => 'https://images.unsplash.com/photo-1593720213428-28a5b9e94613?auto=format&fit=crop&w=1200&h=630',
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                'highlights' => [
                    'Build 5+ real-world projects for your portfolio',
                    'Learn the latest frontend frameworks and libraries',
                    'Master responsive design principles',
                    'Understand web accessibility standards',
                    'Deploy and maintain live web applications',
                ],
                'modules' => [
                    [
                        'week' => 'Week 1-2',
                        'title' => 'HTML & CSS Fundamentals',
                        'topics' => [
                            [
                                'title' => 'HTML5 semantic elements and document structure',
                                'description' => 'Learn how to structure web pages correctly and use semantic HTML5 elements',
                                'duration' => '3 hours',
                                'resources' => [
                                    ['title' => 'Introduction to HTML5', 'type' => 'video'],
                                    ['title' => 'Semantic Elements Cheatsheet', 'type' => 'document'],
                                    ['title' => 'Practice Exercise: Build a Semantic Layout', 'type' => 'exercise'],
                                ],
                            ],
                            [
                                'title' => 'CSS selectors, layouts, and responsive design',
                                'description' => 'Master CSS selectors and create flexible, responsive layouts',
                                'duration' => '4 hours',
                                'resources' => [
                                    ['title' => 'CSS Selectors Deep Dive', 'type' => 'video'],
                                    ['title' => 'Responsive Design Principles', 'type' => 'document'],
                                    ['title' => 'CSS Layout Quiz', 'type' => 'quiz'],
                                ],
                            ],
                            [
                                'title' => 'CSS Flexbox and Grid systems',
                                'description' => 'Learn modern layout techniques with Flexbox and CSS Grid',
                                'duration' => '3 hours',
                                'resources' => [
                                    ['title' => 'Flexbox vs Grid', 'type' => 'video'],
                                    ['title' => 'Flexbox Playground', 'type' => 'exercise'],
                                ],
                            ],
                            [
                                'title' => 'Building your first responsive website',
                                'description' => 'Apply your HTML and CSS knowledge to build a complete responsive website',
                                'duration' => '6 hours',
                                'resources' => [
                                    ['title' => 'Project Specifications', 'type' => 'document'],
                                    ['title' => 'Design Assets', 'type' => 'document'],
                                    ['title' => 'Final Project Submission', 'type' => 'exercise'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'week' => 'Week 3-5',
                        'title' => 'JavaScript Essentials',
                        'topics' => [
                            'JavaScript syntax, variables, and data types',
                            'Functions, objects, and arrays',
                            'DOM manipulation and event handling',
                            'Asynchronous JavaScript (Promises, async/await)',
                            'Creating interactive web elements',
                        ],
                    ],
                    [
                        'week' => 'Week 6-8',
                        'title' => 'React Fundamentals',
                        'topics' => [
                            'Introduction to React and component architecture',
                            'State management and props',
                            'React Hooks (useState, useEffect, useContext)',
                            'Routing with React Router',
                            'Building a single-page application',
                        ],
                    ],
                    [
                        'week' => 'Week 9-10',
                        'title' => 'Advanced React & State Management',
                        'topics' => [
                            'Context API and Redux',
                            'Custom hooks and performance optimization',
                            'API integration and data fetching',
                            'Testing React applications',
                            'Building a full-featured web application',
                        ],
                    ],
                    [
                        'week' => 'Week 11-12',
                        'title' => 'Deployment & Beyond',
                        'topics' => [
                            'Build tools and webpack configuration',
                            'CI/CD pipelines for web applications',
                            'Deploying to cloud platforms (Netlify, Vercel)',
                            'Web performance optimization',
                            'Final project completion and presentation',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Do I need prior coding experience for this course?',
                        'answer' => 'No, this course is designed to take you from absolute beginner to advanced level. We start with the fundamentals and gradually build up to more complex concepts.',
                    ],
                    [
                        'question' => 'What software or tools will I need?',
                        'answer' => 'You\'ll need a computer (Windows, Mac, or Linux) with at least 8GB of RAM, a code editor (we recommend VS Code which is free), and a modern web browser. All software used in the course is free and open source.',
                    ],
                    [
                        'question' => 'Will I get a certificate upon completion?',
                        'answer' => 'Yes, you\'ll receive a certificate of completion that you can add to your resume or LinkedIn profile. Our certificates are recognized by many employers in the tech industry.',
                    ],
                    [
                        'question' => 'What kind of support will I receive during the course?',
                        'answer' => 'You\'ll have access to weekly live Q&A sessions, a dedicated Slack channel for peer support, and 1-on-1 office hours with instructors. Our teaching assistants are also available to help with code reviews and technical issues.',
                    ],
                ],
                'features' => [
                    ['name' => 'Basic coding fundamentals', 'included' => true],
                    ['name' => 'Access to learning materials', 'included' => true],
                    ['name' => 'Community forum access', 'included' => true],
                    ['name' => 'Weekly assignments', 'included' => true],
                    ['name' => 'Email support', 'included' => true],
                    ['name' => '1-on-1 mentorship', 'included' => false],
                    ['name' => 'Project reviews', 'included' => false],
                    ['name' => 'Job placement assistance', 'included' => false],
                    ['name' => 'Certificate of completion', 'included' => false],
                ],
            ],
            [
                'attributes' => [
                    'slug' => 'backend-development',
                    'title' => 'Backend Development',
                    'subtitle' => 'Build powerful server-side applications and APIs with Node.js',
                    'description' => 'Learn server-side programming with Node.js, Express, databases, and API development.',
                    'long_description' => 'Learn how to design and implement robust backend systems that power modern web applications. This course covers server-side programming, database design, API development, authentication, and deployment of scalable backend services.',
                    'icon' => 'Database',
                    'level' => 'Intermediate',
                    'duration' => '10 weeks',
                    'schedule' => 'Mon, Tues & Thurs, 10:00-1:00 PM, 2-5pm',
                    'start_date' => 'June 15, 2025',
                    'price_physical' => 300000,
                    'price_online' => 250000,
                    'rating' => 4.8,
                    'popular' => false,
                    'featured' => false,
                    'popular_feature' => 'Build robust backend systems',
                    'tags' => ['backend', 'node.js', 'databases'],
                    'image' => 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?auto=format&fit=crop&w=1200&h=630',
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                'highlights' => [
                    'Build RESTful and GraphQL APIs from scratch',
                    'Master database design and implementation',
                    'Implement secure user authentication systems',
                    'Deploy scalable backend services to the cloud',
                    'Learn microservices architecture principles',
                ],
                'modules' => [
                    [
                        'week' => 'Week 1-2',
                        'title' => 'Node.js Fundamentals',
                        'topics' => [
                            'Introduction to Node.js and server-side JavaScript',
                            'NPM ecosystem and package management',
                            'Asynchronous programming patterns',
                            'Building a simple HTTP server',
                        ],
                    ],
                    [
                        'week' => 'Week 3-4',
                        'title' => 'Express.js & RESTful APIs',
                        'topics' => [
                            'Express.js framework fundamentals',
                            'Routing and middleware',
                            'RESTful API design principles',
                            'API testing and documentation',
                            'Building a complete REST API',
                        ],
                    ],
                    [
                        'week' => 'Week 5-6',
                        'title' => 'Database Integration',
                        'topics' => [
                            'SQL vs NoSQL databases',
                            'MongoDB and Mongoose ODM',
                            'Database schema design',
                            'CRUD operations and data validation',
                            'Building a data-driven application',
                        ],
                    ],
                    [
                        'week' => 'Week 7-8',
                        'title' => 'Authentication & Security',
                        'topics' => [
                            'User authentication strategies',
                            'JWT implementation and session management',
                            'OAuth and third-party authentication',
                            'Security best practices and common vulnerabilities',
                            'Implementing a secure authentication system',
                        ],
                    ],
                    [
                        'week' => 'Week 9-10',
                        'title' => 'Deployment & Scaling',
                        'topics' => [
                            'Deployment strategies and environments',
                            'Cloud platforms (AWS, Heroku)',
                            'Continuous integration and deployment',
                            'Performance optimization and monitoring',
                            'Final project implementation',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Do I need prior experience with JavaScript?',
                        'answer' => 'Yes, this course requires basic JavaScript knowledge. You should be comfortable with variables, functions, arrays, and objects. Our \'JavaScript Essentials\' course is a good prerequisite if you need to build your JS skills first.',
                    ],
                    [
                        'question' => 'Will I learn about database design?',
                        'answer' => 'Absolutely! We spend two full weeks on database integration, covering both SQL and NoSQL databases, schema design, and best practices for data modeling and querying.',
                    ],
                    [
                        'question' => 'Can I build a complete web application after this course?',
                        'answer' => 'Yes, by the end of the course you\'ll have the skills to build complete backend systems. Combined with frontend knowledge (which isn\'t covered in this course), you\'ll be able to create full-stack applications.',
                    ],
                    [
                        'question' => 'What kind of projects will I build?',
                        'answer' => 'Throughout the course, you\'ll build several projects including a RESTful API, a GraphQL API, an authentication system, and a full backend service. Your final project will be a complete backend system that you can showcase in your portfolio.',
                    ],
                ],
                'features' => [
                    ['name' => 'Server-side programming', 'included' => true],
                    ['name' => 'Database design', 'included' => true],
                    ['name' => 'API development', 'included' => true],
                    ['name' => 'Weekly assignments', 'included' => true],
                    ['name' => 'Email support', 'included' => true],
                    ['name' => '1-on-1 mentorship', 'included' => false],
                    ['name' => 'Project reviews', 'included' => false],
                    ['name' => 'Job placement assistance', 'included' => false],
                    ['name' => 'Certificate of completion', 'included' => true],
                ],
            ],
            [
                'attributes' => [
                    'slug' => 'mobile-app-development',
                    'title' => 'Mobile App Development',
                    'subtitle' => 'Create native-like mobile applications for iOS and Android',
                    'description' => 'Build cross-platform mobile applications using React Native and modern mobile development tools.',
                    'long_description' => 'Learn to build cross-platform mobile applications using React Native. This course teaches you how to create beautiful, performant mobile apps that work on both iOS and Android from a single codebase, saving you time and resources.',
                    'icon' => 'Smartphone',
                    'level' => 'Intermediate',
                    'duration' => '8 weeks',
                    'schedule' => 'Mon, Tues & Thurs, 10:00-1:00 PM, 2-5pm',
                    'start_date' => 'June 15, 2025',
                    'price_physical' => 350000,
                    'price_online' => 250000,
                    'rating' => 4.7,
                    'popular' => false,
                    'featured' => true,
                    'popular_feature' => 'Build apps for iOS and Android',
                    'tags' => ['mobile', 'react native', 'ios', 'android'],
                    'image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=1200&h=630',
                    'is_active' => true,
                    'sort_order' => 3,
                ],
                'highlights' => [
                    'Build mobile apps for iOS and Android with one codebase',
                    'Learn native device feature integration',
                    'Implement complex UI with animations',
                    'Publish your apps to the App Store and Google Play',
                    'Create offline-first mobile experiences',
                ],
                'modules' => [
                    [
                        'week' => 'Week 1-2',
                        'title' => 'React Native Fundamentals',
                        'topics' => [
                            'Introduction to React Native and mobile development',
                            'Setting up the development environment',
                            'React Native components and JSX',
                            'Building your first mobile application',
                        ],
                    ],
                    [
                        'week' => 'Week 3-4',
                        'title' => 'Navigation & UI Components',
                        'topics' => [
                            'Navigation systems in React Native',
                            'UI components and styling',
                            'Forms and user input',
                            'Responsive layouts for different screen sizes',
                            'Building a multi-screen application',
                        ],
                    ],
                    [
                        'week' => 'Week 5-6',
                        'title' => 'Device Features & APIs',
                        'topics' => [
                            'Accessing native device features',
                            'Camera and image manipulation',
                            'Geolocation and maps integration',
                            'Local storage and data persistence',
                            'Building a location-based app',
                        ],
                    ],
                    [
                        'week' => 'Week 7-8',
                        'title' => 'Deployment & Publication',
                        'topics' => [
                            'Testing on physical devices',
                            'Performance optimization for mobile',
                            'App store preparation and submission process',
                            'CI/CD for mobile applications',
                            'Final project completion and showcase',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Is React Native still relevant for mobile development?',
                        'answer' => 'Absolutely! React Native continues to be one of the most popular frameworks for cross-platform mobile development, used by companies like Facebook, Instagram, Walmart, and many others.',
                    ],
                    [
                        'question' => 'Do I need a Mac to develop iOS apps with React Native?',
                        'answer' => 'For the course, you can use either Windows, Mac, or Linux as we\'ll be using Expo for development. However, for publishing to the iOS App Store, you\'ll eventually need access to a Mac for the final submission process.',
                    ],
                    [
                        'question' => 'Will I learn how to monetize my mobile apps?',
                        'answer' => 'Yes, we cover various monetization strategies including in-app purchases, subscriptions, and ad integration as part of the deployment module.',
                    ],
                    [
                        'question' => 'Can I use my existing React knowledge for this course?',
                        'answer' => 'Definitely! If you already know React for web development, you\'ll have a significant head start. We\'ll build on those concepts and show you how they apply to mobile development with React Native.',
                    ],
                ],
                'features' => [
                    ['name' => 'React Native fundamentals', 'included' => true],
                    ['name' => 'Cross-platform development', 'included' => true],
                    ['name' => 'UI/UX for mobile', 'included' => true],
                    ['name' => 'Weekly assignments', 'included' => true],
                    ['name' => 'Email support', 'included' => true],
                    ['name' => '1-on-1 mentorship', 'included' => false],
                    ['name' => 'Project reviews', 'included' => false],
                    ['name' => 'App store publishing guide', 'included' => true],
                    ['name' => 'Certificate of completion', 'included' => true],
                ],
            ],
            [
                'attributes' => [
                    'slug' => 'data-science-fundamentals',
                    'title' => 'Data Science Fundamentals',
                    'subtitle' => 'Analyze data and extract meaningful insights using Python',
                    'description' => 'Introduction to data analysis, visualization, and machine learning concepts with Python.',
                    'long_description' => 'This course introduces you to the world of data science, teaching you how to collect, clean, analyze, and visualize data using Python. You\'ll learn statistical methods, machine learning fundamentals, and how to present your findings effectively.',
                    'icon' => 'BookOpen',
                    'level' => 'Beginner to Intermediate',
                    'duration' => '14 weeks',
                    'schedule' => 'Mon, Tues & Thurs, 10:00-1:00 PM, 2-5pm',
                    'start_date' => 'June 15, 2025',
                    'price_physical' => 300000,
                    'price_online' => 200000,
                    'rating' => 4.9,
                    'popular' => false,
                    'featured' => false,
                    'popular_feature' => 'Learn data analysis skills',
                    'tags' => ['data science', 'python', 'machine learning'],
                    'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&h=630',
                    'is_active' => true,
                    'sort_order' => 4,
                ],
                'highlights' => [
                    'Master Python for data analysis and visualization',
                    'Implement machine learning algorithms from scratch',
                    'Build predictive models for real-world problems',
                    'Create compelling data visualizations',
                    'Complete a portfolio-ready data science project',
                ],
                'modules' => [
                    [
                        'week' => 'Week 1-3',
                        'title' => 'Python for Data Science',
                        'topics' => [
                            'Python fundamentals and environment setup',
                            'Data structures and functions',
                            'NumPy and Pandas libraries',
                            'Data collection and cleaning techniques',
                            'Exploratory data analysis',
                        ],
                    ],
                    [
                        'week' => 'Week 4-6',
                        'title' => 'Data Visualization',
                        'topics' => [
                            'Visualization principles and best practices',
                            'Matplotlib and Seaborn libraries',
                            'Interactive visualizations with Plotly',
                            'Dashboard creation',
                            'Communicating insights through visuals',
                        ],
                    ],
                    [
                        'week' => 'Week 7-10',
                        'title' => 'Statistical Analysis & Machine Learning',
                        'topics' => [
                            'Statistical concepts and hypothesis testing',
                            'Supervised learning algorithms',
                            'Unsupervised learning techniques',
                            'Model evaluation and validation',
                            'Feature engineering and selection',
                        ],
                    ],
                    [
                        'week' => 'Week 11-14',
                        'title' => 'Applied Data Science Project',
                        'topics' => [
                            'Project planning and dataset selection',
                            'Data preprocessing and feature engineering',
                            'Model building and optimization',
                            'Result interpretation and insights',
                            'Final project presentation and documentation',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Is Python programming knowledge required for this course?',
                        'answer' => 'No prior Python knowledge is required. We start with Python basics and gradually build up to more advanced concepts. However, some general programming experience is helpful.',
                    ],
                    [
                        'question' => 'Will this course prepare me for a career in data science?',
                        'answer' => 'This course provides a solid foundation for a data science career. You\'ll learn essential skills and build a portfolio project, though some students may want to take additional specialized courses depending on their career goals.',
                    ],
                    [
                        'question' => 'What kind of computer do I need for this course?',
                        'answer' => 'Any modern Windows, Mac, or Linux computer with at least 8GB of RAM is sufficient. We\'ll be using cloud-based computational resources for more intensive tasks.',
                    ],
                    [
                        'question' => 'Will we cover deep learning and neural networks?',
                        'answer' => 'We introduce the concepts of deep learning, but this course focuses on classical machine learning techniques. For those interested in deep learning, we offer an advanced course specifically on that topic.',
                    ],
                ],
                'features' => [
                    ['name' => 'Python for data analysis', 'included' => true],
                    ['name' => 'Data visualization', 'included' => true],
                    ['name' => 'Machine learning basics', 'included' => true],
                    ['name' => 'Weekly assignments', 'included' => true],
                    ['name' => 'Email support', 'included' => true],
                    ['name' => '1-on-1 mentorship', 'included' => false],
                    ['name' => 'Project reviews', 'included' => false],
                    ['name' => 'Job placement assistance', 'included' => false],
                    ['name' => 'Certificate of completion', 'included' => true],
                ],
            ],
            [
                'attributes' => [
                    'slug' => 'fullstack-development',
                    'title' => 'Fullstack Development',
                    'subtitle' => 'Master both frontend and backend technologies',
                    'description' => 'Master both frontend and backend technologies to build complete web applications from scratch.',
                    'long_description' => 'This comprehensive course covers the entire web development stack, from creating stunning user interfaces to building robust backend services. You\'ll learn how to architect, build, and deploy complete web applications from scratch.',
                    'icon' => 'Code',
                    'level' => 'Intermediate to Advanced',
                    'duration' => '16 weeks',
                    'schedule' => 'Mon, Tues & Thurs, 10:00-1:00 PM, 2-5pm',
                    'start_date' => 'June 15, 2025',
                    'price_physical' => 500000,
                    'price_online' => 400000,
                    'rating' => 4.9,
                    'popular' => true,
                    'featured' => true,
                    'popular_feature' => 'Includes job placement',
                    'tags' => ['fullstack', 'web', 'react', 'node.js'],
                    'image' => 'https://images.unsplash.com/photo-1633356122102-3fe601e05bd2?auto=format&fit=crop&w=1200&h=630',
                    'is_active' => true,
                    'sort_order' => 5,
                ],
                'highlights' => [
                    'Build complete web applications from scratch',
                    'Master both frontend and backend technologies',
                    'Learn database design and implementation',
                    'Implement authentication and authorization',
                    'Deploy and maintain full-stack applications',
                ],
                'modules' => [
                    [
                        'week' => 'Week 1-4',
                        'title' => 'Frontend Fundamentals',
                        'topics' => [
                            'HTML, CSS and JavaScript fundamentals',
                            'Modern JavaScript (ES6+)',
                            'React components and state management',
                            'Responsive design and CSS frameworks',
                        ],
                    ],
                    [
                        'week' => 'Week 5-8',
                        'title' => 'Backend Development',
                        'topics' => [
                            'Node.js fundamentals',
                            'Express.js framework',
                            'RESTful API design',
                            'Authentication and security',
                            'Database integration',
                        ],
                    ],
                    [
                        'week' => 'Week 9-12',
                        'title' => 'Full Stack Integration',
                        'topics' => [
                            'Frontend-backend integration',
                            'State management with Redux',
                            'API consumption and error handling',
                            'Authentication flows',
                            'Payment processing integration',
                        ],
                    ],
                    [
                        'week' => 'Week 13-16',
                        'title' => 'Deployment and Beyond',
                        'topics' => [
                            'Testing and debugging',
                            'Continuous Integration/Continuous Deployment',
                            'Cloud deployment (AWS, Heroku)',
                            'Performance optimization',
                            'Final project development',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Is this course suitable for beginners?',
                        'answer' => 'This course is best for those with some programming experience. If you\'re completely new to coding, we recommend starting with our Frontend Development course first.',
                    ],
                    [
                        'question' => 'What technologies will be covered?',
                        'answer' => 'The course covers HTML, CSS, JavaScript, React, Node.js, Express, MongoDB, SQL databases, authentication systems, and deployment platforms like AWS and Heroku.',
                    ],
                    [
                        'question' => 'Will I be job-ready after completing this course?',
                        'answer' => 'Yes! The curriculum is designed to prepare you for junior to mid-level full-stack developer positions. You\'ll graduate with a portfolio of real-world projects and the skills employers are looking for.',
                    ],
                    [
                        'question' => 'How much time should I dedicate outside of class?',
                        'answer' => 'We recommend at least 15-20 hours per week outside of class time for practice, assignments, and projects to get the most out of the course.',
                    ],
                ],
                'features' => [
                    ['name' => 'Basic coding fundamentals', 'included' => true],
                    ['name' => 'Access to learning materials', 'included' => true],
                    ['name' => 'Community forum access', 'included' => true],
                    ['name' => 'Weekly assignments', 'included' => true],
                    ['name' => 'Email support', 'included' => true],
                    ['name' => '1-on-1 mentorship', 'included' => true],
                    ['name' => 'Project reviews', 'included' => true],
                    ['name' => 'Job placement assistance', 'included' => true],
                    ['name' => 'Certificate of completion', 'included' => true],
                ],
            ],
            [
                'attributes' => [
                    'slug' => 'ui-ux-design',
                    'title' => 'UI/UX Design',
                    'subtitle' => 'Create beautiful and functional user experiences',
                    'description' => 'Learn to design intuitive and engaging user interfaces and experiences using industry-standard tools.',
                    'long_description' => 'Learn to design intuitive and engaging user interfaces and experiences that delight users and meet business goals. This course covers the entire design process from research to final deliverables, using industry-standard tools and methodologies.',
                    'icon' => 'Paintbrush',
                    'level' => 'Beginner to Intermediate',
                    'duration' => '10 weeks',
                    'schedule' => 'Mon, Tues & Thurs, 10:00-1:00 PM, 2-5pm',
                    'start_date' => 'June 15, 2025',
                    'price_physical' => 250000,
                    'price_online' => 200000,
                    'rating' => 4.8,
                    'popular' => false,
                    'featured' => false,
                    'popular_feature' => 'Build your design portfolio',
                    'tags' => ['design', 'ui', 'ux', 'figma'],
                    'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=1200&h=630',
                    'is_active' => true,
                    'sort_order' => 6,
                ],
                'highlights' => [
                    'Master Figma for UI/UX design',
                    'Learn user research and testing methodologies',
                    'Create wireframes, prototypes, and high-fidelity designs',
                    'Understand design systems and component libraries',
                    'Build a professional design portfolio',
                ],
                'modules' => [
                    [
                        'week' => 'Week 1-2',
                        'title' => 'Design Fundamentals',
                        'topics' => [
                            'Principles of design',
                            'Color theory and typography',
                            'Visual hierarchy and layout',
                            'Introduction to Figma',
                        ],
                    ],
                    [
                        'week' => 'Week 3-4',
                        'title' => 'User Experience Design',
                        'topics' => [
                            'User research methods',
                            'Personas and user journeys',
                            'Information architecture',
                            'Wireframing and low-fidelity prototyping',
                        ],
                    ],
                    [
                        'week' => 'Week 5-7',
                        'title' => 'User Interface Design',
                        'topics' => [
                            'UI components and patterns',
                            'Responsive and adaptive design',
                            'Design systems',
                            'Interactive prototyping',
                            'Micro-interactions and animations',
                        ],
                    ],
                    [
                        'week' => 'Week 8-10',
                        'title' => 'Professional Practice',
                        'topics' => [
                            'Usability testing',
                            'Design critique and iteration',
                            'Handoff to developers',
                            'Portfolio development',
                            'Final project completion',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Do I need graphic design experience for this course?',
                        'answer' => 'No prior design experience is required. We start with the fundamentals and gradually build up to more advanced concepts. A good eye for detail and willingness to learn are more important.',
                    ],
                    [
                        'question' => 'What software will I need?',
                        'answer' => 'We primarily use Figma, which has a free tier adequate for the course. You\'ll also use various user research and testing tools, many of which offer free versions.',
                    ],
                    [
                        'question' => 'Will I have a portfolio at the end of the course?',
                        'answer' => 'Yes! You\'ll complete several projects throughout the course, culminating in a comprehensive final project that showcases your skills. These will form the foundation of your professional design portfolio.',
                    ],
                    [
                        'question' => 'How technical do I need to be for UI/UX design?',
                        'answer' => 'While coding skills aren\'t required, understanding technical constraints helps create more realistic designs. We\'ll cover the basics of what\'s technically feasible, but the focus is on design principles and processes.',
                    ],
                ],
                'features' => [
                    ['name' => 'Design principles', 'included' => true],
                    ['name' => 'Figma and design tools', 'included' => true],
                    ['name' => 'User research methods', 'included' => true],
                    ['name' => 'Weekly assignments', 'included' => true],
                    ['name' => 'Email support', 'included' => true],
                    ['name' => '1-on-1 mentorship', 'included' => false],
                    ['name' => 'Portfolio reviews', 'included' => true],
                    ['name' => 'Job placement assistance', 'included' => false],
                    ['name' => 'Certificate of completion', 'included' => true],
                ],
            ],
        ];

        foreach ($courses as $data) {
            $course = Course::create($data['attributes']);

            foreach ($data['highlights'] as $i => $text) {
                $course->highlights()->create([
                    'text' => $text,
                    'sort_order' => $i + 1,
                ]);
            }

            foreach ($data['modules'] as $mi => $module) {
                $courseModule = $course->modules()->create([
                    'week' => $module['week'],
                    'title' => $module['title'],
                    'estimated_hours' => 12,
                    'is_detailed' => $mi === 0,
                    'sort_order' => $mi + 1,
                ]);

                foreach ($module['topics'] as $ti => $topic) {
                    if (is_array($topic)) {
                        $courseTopic = $courseModule->topics()->create([
                            'title' => $topic['title'],
                            'description' => $topic['description'] ?? null,
                            'duration' => $topic['duration'] ?? null,
                            'sort_order' => $ti + 1,
                        ]);

                        foreach ($topic['resources'] ?? [] as $ri => $resource) {
                            $courseTopic->resources()->create([
                                'title' => $resource['title'],
                                'type' => $resource['type'],
                                'sort_order' => $ri + 1,
                            ]);
                        }
                    } else {
                        $courseModule->topics()->create([
                            'title' => $topic,
                            'description' => null,
                            'duration' => null,
                            'sort_order' => $ti + 1,
                        ]);
                    }
                }
            }

            foreach ($data['faqs'] as $i => $faq) {
                $course->faqs()->create([
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'sort_order' => $i + 1,
                ]);
            }

            foreach ($data['features'] as $i => $feature) {
                $course->features()->create([
                    'name' => $feature['name'],
                    'included' => $feature['included'],
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }
}
