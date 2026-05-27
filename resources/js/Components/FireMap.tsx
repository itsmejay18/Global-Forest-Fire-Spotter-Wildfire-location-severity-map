import React from 'react';
import { MapContainer, TileLayer, CircleMarker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { WildfireIncident } from '../types';

interface FireMapProps {
    incidents: WildfireIncident[];
    loading: boolean;
}

const FireMap: React.FC<FireMapProps> = ({ incidents, loading }) => {
    const getMarkerColor = (severity: string) => {
        switch (severity) {
            case 'Extreme': return '#991b1b'; // red-800
            case 'High': return '#dc2626';    // red-600
            case 'Medium': return '#f97316';  // orange-500
            case 'Low': return '#facc15';     // yellow-400
            default: return '#6b7280';       // gray-500
        }
    };

    const getMarkerRadius = (burnedArea: number) => {
        // Base radius + logarithmic scale of burned area to keep it manageable
        return 5 + Math.log10(Math.max(1, burnedArea)) * 5;
    };

    if (loading && incidents.length === 0) {
        return (
            <div className="flex items-center justify-center h-full bg-gray-100">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mx-auto mb-4"></div>
                    <p className="text-lg font-semibold text-gray-700">Loading wildfire data...</p>
                </div>
            </div>
        );
    }

    return (
        <MapContainer
            center={[20, 0]}
            zoom={2}
            minZoom={2}
            maxBounds={L.latLngBounds(L.latLng(-85.05112878, -180), L.latLng(85.05112878, 180))}
            maxBoundsViscosity={1.0}
            worldCopyJump={true}
            className="h-full w-full"
            style={{ minHeight: '100vh' }}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                noWrap={true}
            />
            {incidents.map(incident => (
                <CircleMarker
                    key={incident.id}
                    center={[incident.latitude, incident.longitude]}
                    radius={getMarkerRadius(incident.burned_area)}
                    pathOptions={{
                        fillColor: getMarkerColor(incident.severity),
                        color: getMarkerColor(incident.severity),
                        fillOpacity: 0.6,
                        weight: 1
                    }}
                >
                    <Popup>
                        <div className="p-1">
                            <h3 className="font-bold text-lg">{incident.name}</h3>
                            <p><strong>Country:</strong> {incident.country}</p>
                            <p><strong>Date:</strong> {incident.fire_date}</p>
                            <p><strong>Severity:</strong> <span style={{ color: getMarkerColor(incident.severity), fontWeight: 'bold' }}>{incident.severity}</span></p>
                            <p><strong>Burned Area:</strong> {incident.burned_area.toLocaleString()} ha</p>
                            <p><strong>Duration:</strong> {incident.duration} hours</p>
                        </div>
                    </Popup>
                </CircleMarker>
            ))}
        </MapContainer>
    );
};

export default FireMap;
