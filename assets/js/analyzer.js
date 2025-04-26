(function($) {
    'use strict';
    
    // Attendi che la pagina sia completamente caricata
    $(window).on('load', function() {
        // Verifica se siamo in una pagina Elementor
        if (typeof elementorFrontend !== 'undefined') {
            console.log('Elementor Speed Analyzer: Inizializzazione...');
            setTimeout(analyzeElementorComponents, 1000);
        }
    });
    
    function analyzeElementorComponents() {
        let timingData = {
            widgets: {},
            sections: {},
            page_load: performance.timing.loadEventEnd - performance.timing.navigationStart,
            dom_ready: performance.timing.domComplete - performance.timing.navigationStart
        };
        
        // Analizza ogni sezione Elementor
        $('.elementor-section').each(function(index) {
            const sectionId = $(this).data('id') || 'section-' + index;
            const startTime = performance.now();
            
            // Forza il rendering
            $(this).css('opacity', '0.99').outerHeight();
            $(this).css('opacity', '1');
            
            const endTime = performance.now();
            timingData.sections[sectionId] = {
                time: endTime - startTime,
                elements: $(this).find('.elementor-element').length,
                widgets: {}
            };
            
            // Analizza ogni widget nella sezione
            $(this).find('.elementor-widget').each(function(widgetIndex) {
                const widgetId = $(this).data('id') || 'widget-' + widgetIndex;
                const widgetType = $(this).data('widget_type') || 'sconosciuto';
                const widgetStartTime = performance.now();
                
                // Forza il rendering
                $(this).css('opacity', '0.99').outerHeight();
                $(this).css('opacity', '1');
                
                const widgetEndTime = performance.now();
                const widgetTime = widgetEndTime - widgetStartTime;
                
                // Salva i dati del widget
                timingData.sections[sectionId].widgets[widgetId] = {
                    type: widgetType,
                    time: widgetTime
                };
                
                // Aggrega per tipo di widget
                if (!timingData.widgets[widgetType]) {
                    timingData.widgets[widgetType] = {
                        count: 0,
                        total_time: 0
                    };
                }
                
                timingData.widgets[widgetType].count++;
                timingData.widgets[widgetType].total_time += widgetTime;
            });
        });
        
        // Calcola medie e identifica problemi
        processTimingData(timingData);
        
        // Salva i dati se sei un amministratore
        if (speed_analyzer_vars) {
            const pageId = $('body').attr('data-elementor-id') || 0;
            
            $.ajax({
                url: speed_analyzer_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_elementor_timing',
                    nonce: speed_analyzer_vars.nonce,
                    timing_data: timingData,
                    page_id: pageId
                },
                success: function(response) {
                    console.log('Elementor Speed Analyzer: Dati salvati');
                }
            });
        }
    }
    
    function processTimingData(data) {
        // Identifica i widget più lenti
        let slowWidgets = [];
        
        for (const type in data.widgets) {
            const average = data.widgets[type].total_time / data.widgets[type].count;
            data.widgets[type].average = average;
            
            slowWidgets.push({
                type: type,
                average: average,
                count: data.widgets[type].count
            });
        }
        
        // Ordina per tempo medio
        slowWidgets.sort((a, b) => b.average - a.average);
        data.slowest_widgets = slowWidgets.slice(0, 5);
        
        console.log('Analisi completata:', data);
    }
    
})(jQuery);