<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Government Offices Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1rem 2rem;
            z-index: 10;
        }
        .navbar-brand {
            font-weight: 700;
            color: #1e3a5f;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .map-container {
            flex-grow: 1;
            position: relative;
            display: flex;
        }
        #map {
            width: 100%;
            height: 100%;
            border: none;
        }
        .office-list {
            width: 400px;
            background: #ffffff;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
            overflow-y: auto;
            z-index: 5;
            padding: 1.5rem;
        }
        .office-card {
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .office-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            border-color: #0d6efd;
        }
        .office-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2b3440;
            margin-bottom: 0.5rem;
        }
        .office-detail {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .office-detail i {
            color: #0d6efd;
            margin-top: 3px;
        }
        .service-badge {
            background: #eef2fa;
            color: #4a6bbd;
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            margin-right: 0.3rem;
            margin-bottom: 0.3rem;
            display: inline-block;
            font-weight: 500;
        }
        /* Map custom styling for info window */
        .gm-style .gm-style-iw {
            background-color: #fff !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <div style="background:#0d6efd; color:#fff; border-radius:8px; padding:6px 12px;">
                    <i class="bi bi-building"></i>
                </div>
                Gov Services Directory
            </a>
            <div class="d-flex">
                <a href="{{ route('services.index') }}" class="btn btn-outline-primary me-2">Browse Services</a>
                @auth
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary">My Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="map-container">
        <div class="office-list" id="office-list">
            <h4 class="mb-4 text-dark" style="font-weight:700;">Find an Office</h4>
            <!-- Generated via JS -->
        </div>
        <div id="map"></div>
    </div>

    <!-- Hidden data injected by controller -->
    <script id="offices-data" type="application/json">
        {!! $officesJson !!}
    </script>

    <script>
        const officesData = JSON.parse(document.getElementById('offices-data').textContent);
        let map;
        let markers = [];
        let infoWindow;

        function initMap() {
            // Default center if no offices
            let center = { lat: 33.8938, lng: 35.5018 }; // Default Beirut
            if (officesData.length > 0) {
                center = { lat: officesData[0].lat, lng: officesData[0].lng };
            }

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: center,
                styles: [
                    { "featureType": "all", "elementType": "geometry.fill", "stylers": [{ "weight": "2.00" }] },
                    { "featureType": "all", "elementType": "geometry.stroke", "stylers": [{ "color": "#9c9c9c" }] },
                    { "featureType": "all", "elementType": "labels.text", "stylers": [{ "visibility": "on" }] },
                    { "featureType": "landscape", "elementType": "all", "stylers": [{ "color": "#f2f2f2" }] },
                    { "featureType": "landscape", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }] },
                    { "featureType": "landscape.man_made", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }] },
                    { "featureType": "poi", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                    { "featureType": "road", "elementType": "all", "stylers": [{ "saturation": -100 }, { "lightness": 45 }] },
                    { "featureType": "road", "elementType": "geometry.fill", "stylers": [{ "color": "#eeeeee" }] },
                    { "featureType": "road", "elementType": "labels.text.fill", "stylers": [{ "color": "#7b7b7b" }] },
                    { "featureType": "road", "elementType": "labels.text.stroke", "stylers": [{ "color": "#ffffff" }] },
                    { "featureType": "road.highway", "elementType": "all", "stylers": [{ "visibility": "simplified" }] },
                    { "featureType": "road.arterial", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] },
                    { "featureType": "transit", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                    { "featureType": "water", "elementType": "all", "stylers": [{ "color": "#46bcec" }, { "visibility": "on" }] },
                    { "featureType": "water", "elementType": "geometry.fill", "stylers": [{ "color": "#c8d7d4" }] },
                    { "featureType": "water", "elementType": "labels.text.fill", "stylers": [{ "color": "#070707" }] },
                    { "featureType": "water", "elementType": "labels.text.stroke", "stylers": [{ "color": "#ffffff" }] }
                ]
            });

            infoWindow = new google.maps.InfoWindow();
            const officeListContainer = document.getElementById('office-list');

            officesData.forEach((office, index) => {
                // Add marker
                const marker = new google.maps.Marker({
                    position: { lat: office.lat, lng: office.lng },
                    map: map,
                    title: office.name,
                    animation: google.maps.Animation.DROP
                });

                markers.push(marker);

                // Build info window content
                const contentStr = `
                    <div style="padding: 10px; max-width:250px;">
                        <h6 style="font-weight:700; color:#1e3a5f; margin-bottom:5px;">${office.name}</h6>
                        <p style="font-size:13px; color:#6c757d; margin-bottom:8px;"><i class="bi bi-geo-alt-fill text-primary"></i> ${office.address}</p>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${office.lat},${office.lng}" target="_blank" class="btn btn-sm btn-outline-primary w-100" style="border-radius:20px;">Get Directions</a>
                    </div>
                `;

                marker.addListener("click", () => {
                    infoWindow.setContent(contentStr);
                    infoWindow.open(map, marker);
                    map.panTo(marker.getPosition());
                    map.setZoom(15);
                });

                // Add to sidebar
                const card = document.createElement('div');
                card.className = 'office-card';
                card.onclick = () => {
                    google.maps.event.trigger(marker, 'click');
                };

                let servicesHtml = office.services.slice(0,3).map(s => `<span class="service-badge">${s}</span>`).join('');
                if(office.services.length > 3) {
                    servicesHtml += `<span class="service-badge">+${office.services.length - 3} more</span>`;
                }

                card.innerHTML = `
                    <div class="office-name">${office.name}</div>
                    <div class="office-detail"><i class="bi bi-geo-alt"></i> <span>${office.address}</span></div>
                    <div class="office-detail"><i class="bi bi-clock"></i> <span>${office.working_hours || '9:00 AM - 5:00 PM'}</span></div>
                    <div class="office-detail"><i class="bi bi-telephone"></i> <span>${office.contact_info || 'Not available'}</span></div>
                    <div class="mt-2">${servicesHtml}</div>
                `;
                
                officeListContainer.appendChild(card);
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initMap" async defer></script>
</body>
</html>
