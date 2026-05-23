import React from 'react';
import { Severity, Filters, WildfireIncident } from '../types';
import TrendChart from './TrendChart';

interface SidebarProps {
    filters: Filters;
    setFilters: React.Dispatch<React.SetStateAction<Filters>>;
    incidents: WildfireIncident[];
}

const Sidebar: React.FC<SidebarProps> = ({ filters, setFilters, incidents }) => {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const severities: Severity[] = ['Low', 'Medium', 'High', 'Extreme'];

    const handleMonthChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        setFilters(prev => ({ ...prev, month: e.target.value }));
    };

    const toggleSeverity = (severity: Severity) => {
        setFilters(prev => {
            if (prev.severity.includes(severity)) {
                return { ...prev, severity: prev.severity.filter(s => s !== severity) };
            } else {
                return { ...prev, severity: [...prev.severity, severity] };
            }
        });
    };

    const totalBurnedArea = incidents.reduce((acc, curr) => acc + curr.burned_area, 0);
    
    const severityValues: Record<Severity, number> = {
        'Low': 1,
        'Medium': 2,
        'High': 3,
        'Extreme': 4
    };

    const averageSeverityValue = incidents.length > 0
        ? incidents.reduce((acc, curr) => acc + severityValues[curr.severity], 0) / incidents.length
        : 0;

    const getAverageSeverityLabel = (val: number) => {
        if (val <= 1.5) return 'Low';
        if (val <= 2.5) return 'Medium';
        if (val <= 3.5) return 'High';
        return 'Extreme';
    };

    const getSeverityColor = (severity: string) => {
        switch (severity) {
            case 'Extreme': return 'bg-red-800';
            case 'High': return 'bg-red-600';
            case 'Medium': return 'bg-orange-500';
            case 'Low': return 'bg-yellow-400';
            default: return 'bg-gray-500';
        }
    };

    return (
        <div className="w-80 h-full bg-gray-900 text-white flex flex-col overflow-y-auto shadow-xl">
            <div className="p-6 space-y-8">
                <div>
                    <h1 className="text-2xl font-bold text-red-500 mb-2">Fire Spotter</h1>
                    <p className="text-gray-400 text-sm italic">Global Wildfire Monitoring</p>
                </div>

                {/* Filters Section */}
                <section className="space-y-4">
                    <h2 className="text-lg font-semibold border-b border-gray-700 pb-2">Filters</h2>
                    
                    <div className="space-y-2">
                        <label className="block text-sm font-medium text-gray-400">Month</label>
                        <select
                            value={filters.month}
                            onChange={handleMonthChange}
                            className="w-full bg-gray-800 border border-gray-700 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            <option value="all">All Months</option>
                            {months.map((month, index) => (
                                <option key={month} value={index + 1}>{month}</option>
                            ))}
                        </select>
                    </div>

                    <div className="space-y-2">
                        <label className="block text-sm font-medium text-gray-400">Severity Level</label>
                        <div className="grid grid-cols-2 gap-2">
                            {severities.map(severity => (
                                <button
                                    key={severity}
                                    onClick={() => toggleSeverity(severity)}
                                    className={`py-1 px-2 rounded text-xs font-semibold transition-colors ${
                                        filters.severity.includes(severity)
                                            ? `${getSeverityColor(severity)} text-white`
                                            : 'bg-gray-800 text-gray-400 border border-gray-700 hover:bg-gray-700'
                                    }`}
                                >
                                    {severity}
                                </button>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Statistics Section */}
                <section className="space-y-4">
                    <h2 className="text-lg font-semibold border-b border-gray-700 pb-2">Statistics</h2>
                    <div className="grid grid-cols-1 gap-4">
                        <div className="bg-gray-800 p-4 rounded-lg border border-gray-700">
                            <p className="text-gray-400 text-xs uppercase tracking-wider">Total Incidents</p>
                            <p className="text-2xl font-bold">{incidents.length.toLocaleString()}</p>
                        </div>
                        <div className="bg-gray-800 p-4 rounded-lg border border-gray-700">
                            <p className="text-gray-400 text-xs uppercase tracking-wider">Burned Area</p>
                            <p className="text-2xl font-bold">{totalBurnedArea.toLocaleString()} <span className="text-sm font-normal">ha</span></p>
                        </div>
                        <div className="bg-gray-800 p-4 rounded-lg border border-gray-700">
                            <p className="text-gray-400 text-xs uppercase tracking-wider">Avg. Severity</p>
                            <p className="text-2xl font-bold">{incidents.length > 0 ? getAverageSeverityLabel(averageSeverityValue) : 'N/A'}</p>
                        </div>
                    </div>
                </section>

                {/* Trends Section */}
                <section className="space-y-4">
                    <h2 className="text-lg font-semibold border-b border-gray-700 pb-2">Temporal Trends</h2>
                    <TrendChart incidents={incidents} />
                </section>

                {/* Legend Section */}
                <section className="space-y-4">
                    <h2 className="text-lg font-semibold border-b border-gray-700 pb-2">Legend</h2>
                    
                    <div className="space-y-2">
                        <p className="text-sm font-medium text-gray-400">Severity Colors</p>
                        <div className="space-y-1">
                            {severities.map(severity => (
                                <div key={severity} className="flex items-center space-x-2">
                                    <div className={`w-3 h-3 rounded-full ${getSeverityColor(severity)}`}></div>
                                    <span className="text-xs text-gray-300">{severity}</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <p className="text-sm font-medium text-gray-400">Burned Area (Scale)</p>
                        <div className="flex items-end space-x-4 h-12">
                            <div className="flex flex-col items-center">
                                <div className="w-2 h-2 rounded-full bg-gray-500 mb-1"></div>
                                <span className="text-[10px] text-gray-400">Small</span>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="w-4 h-4 rounded-full bg-gray-500 mb-1"></div>
                                <span className="text-[10px] text-gray-400">Medium</span>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="w-8 h-8 rounded-full bg-gray-500 mb-1"></div>
                                <span className="text-[10px] text-gray-400">Large</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <div className="mt-auto p-6 text-center text-gray-600 text-[10px] border-t border-gray-800">
                &copy; 2026 Global Forest Fire Spotter
            </div>
        </div>
    );
};

export default Sidebar;
