export type Severity = 'Low' | 'Medium' | 'High' | 'Extreme';

export interface WildfireIncident {
    id: number;
    latitude: number;
    longitude: number;
    burned_area: number;
    fire_date: string;
    severity: Severity;
    country: string;
    month: number;
    year: number;
    duration: number;
    name: string;
}

export interface GeoJsonFeature {
    type: 'Feature';
    geometry: {
        type: 'Point';
        coordinates: [number, number]; // [longitude, latitude]
    };
    properties: {
        id: number;
        name: string;
        burned_area: number;
        fire_date: string;
        severity: Severity;
        country: string;
        duration: number;
        month: number;
        year: number;
    };
}

export interface GeoJsonFeatureCollection {
    type: 'FeatureCollection';
    features: GeoJsonFeature[];
}

export interface Filters {
    month: string; // 'all' or '1'-'12'
    severity: Severity[];
}
