import '../css/app.css';

import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import FireMap from './Components/FireMap';
import Sidebar from './Components/Sidebar';
import { Filters, WildfireIncident, GeoJsonFeatureCollection } from './types';

const App = () => {
    const [filters, setFilters] = useState<Filters>({
        month: 'all',
        severity: ['Low', 'Medium', 'High', 'Extreme']
    });

    const [incidents, setIncidents] = useState<WildfireIncident[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchIncidents = async () => {
            setLoading(true);
            try {
                const params = new URLSearchParams();
                if (filters.month !== 'all') {
                    params.append('month', filters.month);
                }
                // The API currently only supports one severity at a time in the query param
                // or we could filter client-side. For now, let's fetch all and filter client-side
                // to make it more responsive, or we can update the API later.
                // Given the current API implementation in WildfireIncidentController, 
                // it only handles single 'severity' param.
                
                const response = await fetch(`/api/incidents?${params.toString()}`);
                const data: GeoJsonFeatureCollection = await response.json();
                
                const mappedIncidents: WildfireIncident[] = data.features.map(feature => ({
                    id: feature.properties.id,
                    latitude: feature.geometry.coordinates[1],
                    longitude: feature.geometry.coordinates[0],
                    burned_area: feature.properties.burned_area,
                    fire_date: feature.properties.fire_date,
                    severity: feature.properties.severity,
                    country: feature.properties.country,
                    month: feature.properties.month,
                    year: feature.properties.year,
                    duration: feature.properties.duration,
                    name: feature.properties.name,
                }));

                setIncidents(mappedIncidents);
            } catch (error) {
                console.error('Error fetching incidents:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchIncidents();
    }, [filters.month]); // Re-fetch only when month changes. Severity filtered client-side for better UX.

    const filteredIncidents = incidents.filter(incident => 
        filters.severity.includes(incident.severity)
    );

    return (
        <div className="flex h-screen w-full overflow-hidden bg-gray-100">
            <Sidebar 
                filters={filters} 
                setFilters={setFilters} 
                incidents={filteredIncidents} 
            />
            <main className="flex-1 relative">
                <FireMap 
                    incidents={filteredIncidents} 
                    loading={loading} 
                />
            </main>
        </div>
    );
};

const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}
