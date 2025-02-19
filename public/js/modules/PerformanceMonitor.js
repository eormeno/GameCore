export class PerformanceMonitor {
    constructor(metricIds) {
        this.metrics = {
            frontend: this.initMetrics('frontend', metricIds),
            backend: this.initMetrics('backend', metricIds)
        };
        this.historySize = 10;
    }

    initMetrics(type, ids) {
        return {
            avg: { element: document.getElementById(ids[`${type}Avg`]), values: [] },
            min: { element: document.getElementById(ids[`${type}Min`]), values: [] },
            max: { element: document.getElementById(ids[`${type}Max`]), values: [] }
        };
    }

    record(type, value) {
        if (!value || value < 0) return;

        const metric = this.metrics[type];
        this.updateMetricData(metric, value);
        this.updateDisplay(metric);
    }

    updateMetricData(metric, value) {
        ['avg', 'min', 'max'].forEach(key => {
            metric[key].values.push(value);
            if (metric[key].values.length > this.historySize) metric[key].values.shift();
        });
    }

    updateDisplay(metric) {
        metric.avg.element.textContent = `${this.calculateAverage(metric.avg.values)} ms`;
        metric.min.element.textContent = `${Math.min(...metric.min.values)} ms`;
        metric.max.element.textContent = `${Math.max(...metric.max.values)} ms`;
    }

    calculateAverage(values) {
        return values.length > 0
            ? Math.round(values.reduce((sum, val) => sum + val, 0) / values.length)
            : 0;
    }
}
