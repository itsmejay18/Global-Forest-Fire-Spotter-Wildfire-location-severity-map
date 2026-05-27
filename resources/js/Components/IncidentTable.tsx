import React, { useMemo } from 'react';
import { WildfireIncident } from '../types';

interface IncidentTableProps {
    incidents: WildfireIncident[];
}

const IncidentTable: React.FC<IncidentTableProps> = ({ incidents }) => {
    const rows = useMemo(() => {
        return [...incidents].sort((a, b) => {
            if (a.fire_date < b.fire_date) return 1;
            if (a.fire_date > b.fire_date) return -1;
            return b.burned_area - a.burned_area;
        });
    }, [incidents]);

    return (
        <div className="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div className="max-h-80 overflow-auto">
                <table className="min-w-full text-xs">
                    <thead className="sticky top-0 bg-gray-900">
                        <tr className="text-gray-300">
                            <th className="text-left font-semibold px-3 py-2">Name</th>
                            <th className="text-left font-semibold px-3 py-2">Country</th>
                            <th className="text-left font-semibold px-3 py-2">Date</th>
                            <th className="text-left font-semibold px-3 py-2">Severity</th>
                            <th className="text-right font-semibold px-3 py-2">Area (ha)</th>
                            <th className="text-right font-semibold px-3 py-2">Lat</th>
                            <th className="text-right font-semibold px-3 py-2">Lng</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((i) => (
                            <tr key={i.id} className="border-t border-gray-700 text-gray-200">
                                <td className="px-3 py-2 whitespace-nowrap">{i.name}</td>
                                <td className="px-3 py-2 whitespace-nowrap">{i.country}</td>
                                <td className="px-3 py-2 whitespace-nowrap">{i.fire_date}</td>
                                <td className="px-3 py-2 whitespace-nowrap">{i.severity}</td>
                                <td className="px-3 py-2 text-right whitespace-nowrap">{i.burned_area.toLocaleString()}</td>
                                <td className="px-3 py-2 text-right whitespace-nowrap">{i.latitude.toFixed(4)}</td>
                                <td className="px-3 py-2 text-right whitespace-nowrap">{i.longitude.toFixed(4)}</td>
                            </tr>
                        ))}
                        {rows.length === 0 && (
                            <tr>
                                <td className="px-3 py-6 text-center text-gray-400" colSpan={7}>
                                    No incidents to display.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default IncidentTable;

