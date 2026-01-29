<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ananya - Where Telugu Meets Technology</title>
    <meta name="description" content="Ananya API provides comprehensive Telugu and English text processing capabilities for word games, linguistic analysis, and educational applications.">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #f97316;
            --accent-color: #fbbf24;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
        }
        

        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 70vh;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="75" cy="25" r="1" fill="%23ffffff" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .brand-name {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .tagline {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .stats-section {
            background: var(--dark-color);
            color: white;
            padding: 4rem 0;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .cta-section {
            background: linear-gradient(45deg, var(--light-color) 0%, #e2e8f0 100%);
            padding: 5rem 0;
        }
        
        .btn-primary-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        

        
        .api-demo {
            background: #1e293b;
            border-radius: 10px;
            padding: 1.5rem;
            color: #e2e8f0;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.9rem;
            margin: 2rem 0;
        }
        
        .response-success {
            color: #22c55e;
        }
        
        .response-key {
            color: #f59e0b;
        }
        
        .response-string {
            color: #06b6d4;
        }
    </style>
    
    <!-- Include Ananya Header Component -->
    <?php include 'includes/header.php'; ?>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <h1 class="brand-name">Ananya</h1>
                        <p class="tagline">Where Telugu Meets Technology</p>
                        <p class="lead mb-4">
                            Unlock the power of Telugu and English text processing with our comprehensive API. 
                            Built for developers creating word games, educational apps, and linguistic tools.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="docs/api.php" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-book me-2"></i> Explore Documentation
                            </a>
                            <a href="docs/swagger.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-play me-2"></i> Try Interactive Demo
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="api-demo">
                        <div class="mb-3">
                            <span class="text-muted">// Try our API</span><br>
                            <span class="text-info">curl</span> "https://ananya.telugupuzzles.com/api/<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;text/length?string=హలో&language=telugu"
                        </div>
                        <div class="response-success">
                            {<br>
                            &nbsp;&nbsp;<span class="response-key">"response_code"</span>: <span class="response-success">200</span>,<br>
                            &nbsp;&nbsp;<span class="response-key">"message"</span>: <span class="response-string">"Length Calculated"</span>,<br>
                            &nbsp;&nbsp;<span class="response-key">"string"</span>: <span class="response-string">"హలో"</span>,<br>
                            &nbsp;&nbsp;<span class="response-key">"language"</span>: <span class="response-string">"Telugu"</span>,<br>
                            &nbsp;&nbsp;<span class="response-key">"data"</span>: <span class="response-success">3</span><br>
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-number">50+</div>
                    <h5>API Endpoints</h5>
                    <p class="text-muted">Comprehensive text processing functions</p>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number">2</div>
                    <h5>Languages</h5>
                    <p class="text-muted">Telugu and English support</p>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number">100%</div>
                    <h5>REST API</h5>
                    <p class="text-muted">Easy integration and JSON responses</p>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-number">∞</div>
                    <h5>Possibilities</h5>
                    <p class="text-muted">Build amazing Telugu applications</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2 class="display-5 fw-bold mb-3">Powerful Features for Developers</h2>
                    <p class="lead text-muted">Everything you need to build Telugu language applications</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-language"></i>
                        </div>
                        <h5 class="card-title">Language Processing</h5>
                        <p class="card-text">
                            Advanced Telugu script processing with proper handling of complex character combinations,
                            vowel marks, and logical character representation.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h5 class="card-title">Word Games & Puzzles</h5>
                        <p class="card-text">
                            Specialized functions for anagram detection, palindrome checking, word ladders,
                            and other puzzle game mechanics perfect for Telugu applications.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="card-title">Text Analysis</h5>
                        <p class="card-text">
                            Comprehensive text analysis including character counting, string comparison,
                            pattern matching, and linguistic analysis tools.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5 class="card-title">Educational Tools</h5>
                        <p class="card-text">
                            Perfect for building Telugu learning applications with word strength calculation,
                            language detection, and interactive educational features.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h5 class="card-title">Developer Friendly</h5>
                        <p class="card-text">
                            RESTful API design with comprehensive documentation, interactive testing tools,
                            and ready-to-use code examples in multiple languages.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h5 class="card-title">Mobile Ready</h5>
                        <p class="card-text">
                            Optimized for mobile applications with fast response times,
                            lightweight payloads, and cross-platform compatibility.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-bold mb-4">Ready to Build Something Amazing?</h2>
                    <p class="lead mb-4">
                        Join developers worldwide who are using Ananya API to create innovative Telugu language applications.
                        Start building today with our comprehensive documentation and interactive tools.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="docs/api.php" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-rocket me-2"></i> Get Started
                        </a>
                        <a href="docs/swagger.php" class="btn btn-outline-dark btn-lg">
                            <i class="fas fa-play me-2"></i> Try Interactive Demo
                        </a>
                        <a href="api_testing.php" class="btn btn-outline-dark btn-lg">
                            <i class="fas fa-flask me-2"></i> Test APIs
                        </a>
                    </div>
                    
                    <div class="mt-5">
                        <p class="text-muted mb-3">Quick Start Example:</p>
                        <div class="bg-dark text-light p-3 rounded text-start" style="font-family: monospace;">
                            <span class="text-warning">// JavaScript Example</span><br>
                            <span class="text-info">fetch</span>(<span class="text-success">'https://ananya.telugupuzzles.com/api/text/length?string=అనన్య&language=Telugu'</span>)<br>
                            &nbsp;&nbsp;.<span class="text-info">then</span>(response => response.<span class="text-info">json</span>())<br>
                            &nbsp;&nbsp;.<span class="text-info">then</span>(data => <span class="text-info">console.log</span>(data.data)); <span class="text-muted">// Output: 4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <strong>Ananya</strong> <small class="text-muted">API</small>
                    </h5>
                    <p class="text-muted">
                        Where Telugu Meets Technology. Empowering developers to create innovative
                        Telugu language applications with powerful processing capabilities.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted"><i class="fab fa-github fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Documentation</h6>
                    <ul class="list-unstyled">
                        <li><a href="docs/api.php" class="text-muted text-decoration-none">API Reference</a></li>
                        <li><a href="docs/swagger.php" class="text-muted text-decoration-none">Interactive Docs</a></li>
                        <li><a href="docs/API_Reference.md" class="text-muted text-decoration-none">Markdown Guide</a></li>
                        <li><a href="docs/openapi.yaml" class="text-muted text-decoration-none">OpenAPI Spec</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Tools</h6>
                    <ul class="list-unstyled">
                        <li><a href="analyzer.php" class="text-muted text-decoration-none">Analyzer</a></li>
                        <li><a href="finder.php" class="text-muted text-decoration-none">Finder</a></li>
                        <li><a href="api_testing.php" class="text-muted text-decoration-none">API Testing</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Collections</h6>
                    <ul class="list-unstyled">
                        <li><a href="docs/postman_collection.json" class="text-muted text-decoration-none">Postman</a></li>
                        <li><a href="docs/thunder_collection.json" class="text-muted text-decoration-none">Thunder Client</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="mailto:support@telugupuzzles.com" class="text-muted text-decoration-none">Email</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">GitHub Issues</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2025 Telugu Puzzles. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <span class="telugu-text">అనన్య</span> - Uniquely Powerful Telugu Processing
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>

</body>
</html>