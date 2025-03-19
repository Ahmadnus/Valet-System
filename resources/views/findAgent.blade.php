<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Valet Hosts</title>

    <!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- Leaflet Extra Markers -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-extra-markers@1.2.1/dist/css/leaflet.extra-markers.min.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet-extra-markers@1.2.1/dist/js/leaflet.extra-markers.min.js"></script>

<style>
    #map { height: 600px; width: 100%; }
</style>

</head>
<body>
    <div id="map"></div>
    <div id="agentsList" style="display:none;">
        <h3>Select an Agent:</h3>
        <ul id="agents"></ul>
        <button id="sendRequest" style="display:none;">Send Request</button>
    </div>


    <script>
    let map = L.map('map').setView([25.276987, 55.296249], 12); // Default Dubai

    // Load OpenStreetMap Tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom Host Marker (Blue)
    let hostIcon = L.ExtraMarkers.icon({
        icon: 'fa-building',
        markerColor: 'blue',
        shape: 'square',
        prefix: 'fa'
    });

    // Custom Agent Marker (Green)
    let agentIcon = L.ExtraMarkers.icon({
        icon: 'fa-user',
        markerColor: 'green',
        shape: 'circle',
        prefix: 'fa'
    });

    // Fetch Hosts from Laravel API
    fetch('/api/hosts')
        .then(response => response.json())
        .then(hosts => {
            hosts.forEach(host => {
                let marker = L.marker([host.latitude, host.longitude], { icon: hostIcon })
                    .addTo(map)
                    .bindPopup(`<b>${host.name}</b><br><button onclick="loadAgents(${host.id})">View Agents</button>`);
            });
        })
        .catch(error => console.error("Error fetching hosts:", error));

    function loadAgents(host_id) {
        fetch(`/api/hosts/${host_id}/agents`)
            .then(response => response.json())
            .then(agents => {
                agents.forEach(agent => {
                    L.marker([agent.latitude, agent.longitude], { icon: agentIcon })
                        .addTo(map)
                        .bindPopup(`<b>${agent.name}</b><br><button onclick="selectAgent(${agent.id})">Request Valet</button>`);
                });
            })
            .catch(error => console.error("Error fetching agents:", error));
    }

    function selectAgent(agent_id) {
        alert("Agent selected: " + agent_id);
    }
</script>
</body>
</html>
