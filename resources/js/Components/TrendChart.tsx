import React, { useMemo } from 'react';
import { 
    ResponsiveContainer, 
    ComposedChart, 
    Line, 
    Bar, 
    XAxis, 
    YAxis, 
    CartesianGrid, 
    Tooltip, 
    Legend 
} from 'recharts';
import { WildfireIncident } from '../types';

interface TrendChartProps {
    incidents: WildfireIncident[];
}

const TrendChart: React.FC<TrendChartProps> = ({ incidents }) => {
    const data = useMemo(() => {
        const monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        const aggregation = Array.from({ length: 12 }, (_, i) => ({
            name: monthNames[i],
            count: 0,
            burnedArea: 0,
        }));

        incidents.forEach(incident => {
            const monthData = aggregation[incident.month - 1];
            if (monthData) {
                monthData.count += 1;
                monthData.burnedArea += Math.round(incident.burned_area);
            }
        });

        return aggregation;
    }, [incidents]);

    return (
        <div className="h-64 w-full bg-gray-800 p-2 rounded-lg border border-gray-700">
            <ResponsiveContainer width="100%" height="100%">
                <ComposedChart
                    data={data}
                    margin={{ top: 10, right: 0, bottom: 0, left: -20 }}
                >
                    <CartesianGrid strokeDasharray="3 3" stroke="#374151" vertical={false} />
                    <XAxis 
                        dataKey="name" 
                        stroke="#9CA3AF" 
                        fontSize={10} 
                        tickLine={false}
                        axisLine={false}
                    />
                    <YAxis 
                        yAxisId="left"
                        stroke="#9CA3AF" 
                        fontSize={10} 
                        tickLine={false}
                        axisLine={false}
                    />
                    <YAxis 
                        yAxisId="right" 
                        orientation="right" 
                        stroke="#9CA3AF" 
                        fontSize={10} 
                        tickLine={false}
                        axisLine={false}
                        hide={true} // Hide right axis to save space but keep it for scaling
                    />
                    <Tooltip 
                        contentStyle={{ backgroundColor: '#1F2937', borderColor: '#374151', fontSize: '12px', color: '#fff' }}
                        itemStyle={{ fontSize: '12px' }}
                        labelStyle={{ color: '#9CA3AF', marginBottom: '4px' }}
                    />
                    <Legend 
                        wrapperStyle={{ fontSize: '10px', paddingTop: '10px' }}
                        verticalAlign="bottom"
                        height={36}
                    />
                    <Bar 
                        yAxisId="left" 
                        dataKey="count" 
                        name="Incidents" 
                        fill="#EF4444" 
                        radius={[2, 2, 0, 0]} 
                        barSize={12}
                    />
                    <Line 
                        yAxisId="right" 
                        type="monotone" 
                        dataKey="burnedArea" 
                        name="Burned Area" 
                        stroke="#F59E0B" 
                        dot={false} 
                        strokeWidth={2} 
                    />
                </ComposedChart>
            </ResponsiveContainer>
        </div>
    );
};

export default TrendChart;
