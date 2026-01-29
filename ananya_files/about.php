<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Ananya Telugu Text Processing Platform</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 50%, #f97316 100%);
            color: white;
            padding: 4rem 0;
            margin-top: -20px;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #2563eb, #f97316);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            color: white;
            font-size: 1.5rem;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 3rem 0;
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #2563eb, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .timeline {
            position: relative;
            padding: 2rem 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 12px;
            height: 12px;
            background: #2563eb;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #2563eb;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 12px;
            width: 2px;
            height: calc(100% + 20px);
            background: #e9ecef;
        }
        .timeline-item:last-child::after {
            display: none;
        }
        .telugu-text {
            font-family: 'Noto Sans Telugu', sans-serif;
            font-size: 1.2rem;
            color: #7c3aed;
            text-align: center;
            margin: 2rem 0;
        }
        .contact-card {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php 
    $root_path = '';
    $css_path = 'css/';
    include 'includes/header.php'; 
    ?>
    
    <div class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8 mx-auto text-center">
                        <h1 class="display-4 fw-bold mb-4">About Ananya</h1>
                        <p class="lead mb-4">Bridging the gap between Indic languages and modern technology through innovative text processing solutions.</p>
                        <div class="telugu-text">
                            అనన్య - భారతీయ భాషలు మరియు ఆధునిక టెక్నాలజీ మధ్య వంతెన
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About the Project -->
        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="text-center mb-5">What is Ananya?</h2>
                        <p class="lead text-center mb-4">
                            Ananya is a comprehensive Indic language text processing platform that provides powerful APIs and tools for analyzing, manipulating, and understanding Indic scripts at a deep linguistic level.
                        </p>
                        <p class="text-muted text-center">
                            Named after the Sanskrit word meaning "unique" or "incomparable," Ananya represents our commitment to creating unparalleled solutions for Indic language processing in the digital age. Currently optimized for Telugu with extensible architecture for other Indic languages.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5">Key Features</h2>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-language"></i>
                            </div>
                            <h4>Advanced Text Analysis</h4>
                            <p>Deep linguistic analysis of Indic scripts including character-level processing, logical character extraction, and Unicode normalization across multiple languages.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <h4>RESTful API</h4>
                            <p>Comprehensive REST API with 50+ endpoints for text manipulation, analysis, and processing operations.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h4>Pattern Matching</h4>
                            <p>Advanced pattern recognition and matching algorithms for Indic scripts including anagram detection and word analysis across multiple languages.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h4>Text Metrics</h4>
                            <p>Comprehensive text analysis including word strength calculation, character frequency analysis, and linguistic metrics.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h4>Text Processing</h4>
                            <p>Rich set of text manipulation tools including reversing, randomization, character replacement, and string operations.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4>Cross-Platform</h4>
                            <p>Works seamlessly across web, mobile, and desktop applications with consistent API responses and documentation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <h5>API Endpoints</h5>
                            <p class="text-muted">Comprehensive text processing functions</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-item">
                            <div class="stat-number">100%</div>
                            <h5>Unicode Support</h5>
                            <p class="text-muted">Full Telugu Unicode range coverage</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <h5>API Availability</h5>
                            <p class="text-muted">Reliable and consistent service</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-item">
                            <div class="stat-number">∞</div>
                            <h5>Possibilities</h5>
                            <p class="text-muted">Endless applications for developers</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Development Timeline -->
        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="text-center mb-5">Development Journey</h2>
                        <div class="timeline">
                            <div class="timeline-item">
                                <h5>Conception & Planning</h5>
                                <p class="text-muted">Identified the need for robust Telugu text processing tools and began architectural planning for a comprehensive API platform.</p>
                            </div>
                            <div class="timeline-item">
                                <h5>Core API Development</h5>
                                <p class="text-muted">Built the foundational text processing algorithms including logical character parsing, Unicode handling, and basic string operations.</p>
                            </div>
                            <div class="timeline-item">
                                <h5>Advanced Features</h5>
                                <p class="text-muted">Added sophisticated features like anagram detection, pattern matching, word analysis, and comprehensive text metrics calculation.</p>
                            </div>
                            <div class="timeline-item">
                                <h5>Documentation & Testing</h5>
                                <p class="text-muted">Created comprehensive API documentation, interactive testing tools, and extensive test suites to ensure reliability.</p>
                            </div>
                            <div class="timeline-item">
                                <h5>Platform Launch</h5>
                                <p class="text-muted">Launched the complete Ananya platform with full API access, documentation, and developer tools for the community.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About the Developer -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="text-center mb-5">About the Developer</h2>
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-4">
                                <div class="feature-icon mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <p class="lead">
                                    Ananya is developed by a passionate technologist with deep expertise in natural language processing, 
                                    API development, and Telugu language technology.
                                </p>
                                <p>
                                    With years of experience in software development and a strong connection to Indic cultures, 
                                    the goal was to create tools that preserve and enhance the digital presence of Indic languages 
                                    while making them accessible to modern applications and developers worldwide.
                                </p>
                                <div class="mt-4">
                                    <h5>Specializations:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i> Natural Language Processing</li>
                                        <li><i class="fas fa-check text-success me-2"></i> API Architecture & Development</li>
                                        <li><i class="fas fa-check text-success me-2"></i> Indic Language Technology</li>
                                        <li><i class="fas fa-check text-success me-2"></i> Unicode & Text Processing</li>
                                        <li><i class="fas fa-check text-success me-2"></i> Full-Stack Web Development</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <div class="feature-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h4>Our Mission</h4>
                            <p>
                                To provide developers and organizations with powerful, reliable, and easy-to-use tools 
                                for Indic language text processing, enabling the creation of innovative applications that serve 
                                Indic-speaking communities worldwide.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <div class="feature-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h4>Our Vision</h4>
                            <p>
                                To become the leading platform for Indic language technology, fostering digital innovation 
                                while preserving the rich linguistic heritage of Indic languages for future generations in the digital age.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto">
                        <div class="contact-card">
                            <h3 class="mb-4">Get in Touch</h3>
                            <p class="mb-4">
                                Have questions about Ananya? Need help implementing Indic language text processing in your application? 
                                We'd love to hear from you!
                            </p>
                            <div class="row text-center">
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-envelope fa-2x mb-2"></i>
                                    <h6>Email</h6>
                                    <small>Contact via documentation</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-code fa-2x mb-2"></i>
                                    <h6>API Support</h6>
                                    <small>Technical assistance</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-heart fa-2x mb-2"></i>
                                    <h6>Community</h6>
                                    <small>Open source contributions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>