<div class="wrap">
    <h1>Elementor Speed Analyzer</h1>
    
    <div class="notice notice-info">
        <p>Naviga sul tuo sito e visualizza le pagine costruite con Elementor per raccogliere dati sulle performance.</p>
        <p><strong>Nota:</strong> Per risultati più accurati, assicurati di essere loggato come amministratore durante la navigazione.</p>
    </div>
    
    <div class="card">
        <h2>Istruzioni</h2>
        <ol>
            <li>Visita le pagine del tuo sito costruite con Elementor</li>
            <li>Lo strumento raccoglierà automaticamente i dati di performance</li>
            <li>Torna qui per visualizzare l'analisi dettagliata</li>
        </ol>
        <p>Per un'analisi approfondita, visita più pagine diverse del tuo sito.</p>
    </div>
    
    <?php
    // Recupera le pagine analizzate
    $args = array(
        'post_type' => 'any',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_elementor_speed_data',
                'compare' => 'EXISTS'
            )
        )
    );
    
    $analyzed_pages = get_posts($args);
    
    if (!empty($analyzed_pages)) {
        echo '<h2>Pagine analizzate</h2>';
        echo '<div class="tablenav top">';
        echo '<div class="alignleft actions">';
        echo '<select id="page-selector">';
        echo '<option value="">Seleziona una pagina...</option>';
        
        foreach ($analyzed_pages as $page) {
            echo '<option value="' . esc_attr($page->ID) . '">' . esc_html($page->post_title) . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        echo '</div>';
        
        echo '<div id="analysis-results">';
        echo '<p>Seleziona una pagina per visualizzare i risultati dell\'analisi.</p>';
        echo '</div>';
        
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#page-selector').on('change', function() {
                const pageId = $(this).val();
                
                if (!pageId) {
                    $('#analysis-results').html('<p>Seleziona una pagina per visualizzare i risultati dell\'analisi.</p>');
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_elementor_analysis',
                        page_id: pageId,
                        nonce: '<?php echo wp_create_nonce('get_elementor_analysis_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            renderAnalysisResults(response.data);
                        } else {
                            $('#analysis-results').html('<p>Errore nel caricamento dei dati.</p>');
                        }
                    }
                });
            });
            
            function renderAnalysisResults(data) {
                let html = '<h3>Risultati dell\'analisi</h3>';
                
                html += '<div class="card"><h4>Tempi di caricamento generali</h4>';
                html += '<p>Tempo di caricamento pagina: ' + (data.page_load / 1000).toFixed(2) + ' secondi</p>';
                html += '<p>DOM pronto: ' + (data.dom_ready / 1000).toFixed(2) + ' secondi</p></div>';
                
                html += '<div class="card"><h4>Widget più lenti</h4><table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr><th>Tipo widget</th><th>Tempo medio (ms)</th><th>Occorrenze</th></tr></thead><tbody>';
                
                data.slowest_widgets.forEach(function(widget) {
                    html += '<tr>';
                    html += '<td>' + widget.type + '</td>';
                    html += '<td>' + widget.average.toFixed(2) + '</td>';
                    html += '<td>' + widget.count + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                
                html += '<div class="card">';
                html += '<h4>Consigli per l\'ottimizzazione</h4>';
                html += '<ul>';
                
                // Widget più lento
                if (data.slowest_widgets.length > 0) {
                    const slowestWidget = data.slowest_widgets[0];
                    html += '<li><strong>Widget problematico:</strong> ' + slowestWidget.type + ' con tempo medio di ' + slowestWidget.average.toFixed(2) + 'ms</li>';
                    
                    // Consigli specifici basati sul tipo di widget
                    if (slowestWidget.type.includes('image')) {
                        html += '<li>Ottimizza le immagini (dimensioni e compressione)</li>';
                    } else if (slowestWidget.type.includes('gallery')) {
                        html += '<li>Riduci il numero di immagini o usa il lazy loading</li>';
                    } else if (slowestWidget.type.includes('video')) {
                        html += '<li>Considera di utilizzare thumbnail con caricamento on-demand</li>';
                    } else if (slowestWidget.type.includes('slider') || slowestWidget.type.includes('carousel')) {
                        html += '<li>Usa meno slide o ottimizza le immagini all\'interno</li>';
                    }
                }
                
                html += '<li>Riduci l\'uso di widget complessi o animazioni</li>';
                html += '<li>Considera di attivare un plugin di caching</li>';
                html += '<li>Minimizza CSS e JavaScript</li>';
                html += '</ul>';
                
                html += '<p><strong>Per una guida completa all\'ottimizzazione, consulta il capitolo 9 del libro:</strong></p>';
                html += '<p><a href="https://www.amazon.it/dp/CODICE-ISBN-LIBRO" target="_blank" class="button button-primary">Scopri "Da Zero a Eroe: WordPress con Elementor senza lacrime (o quasi)"</a></p>';
                html += '</div>';
                
                $('#analysis-results').html(html);
            }
        });
        </script>
        <?php
    } else {
        echo '<div class="notice notice-warning">';
        echo '<p>Non sono stati ancora raccolti dati di analisi. Visita alcune pagine del tuo sito costruite con Elementor per iniziare.</p>';
        echo '</div>';
    }
    ?>
    
    <div class="card">
        <h2>Scopri come risolvere questi problemi</h2>
        <p>Questo strumento ti aiuta a <strong>identificare</strong> i problemi di performance, ma per <strong>risolverli</strong> definitivamente hai bisogno di una guida completa.</p>
        <p>Nel capitolo 9 del libro <strong>"Da Zero a Eroe: WordPress con Elementor senza lacrime (o quasi)"</strong> troverai:</p>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li>Tecniche avanzate di ottimizzazione per Elementor</li>
            <li>Come risolvere i widget problematici senza perderti funzionalità</li>
            <li>Configurazioni ottimali per caching e compressione</li>
            <li>Alternative leggere ai widget più pesanti</li>
        </ul>
        <p><a href="https://www.amazon.it/dp/B0F6BZ6LZV" target="_blank" class="button button-primary button-hero">Acquista il libro su Amazon</a></p>
    </div>
    
    <div class="notice notice-info">
        <p>Questo strumento è offerto gratuitamente dall'autore di "Da Zero a Eroe: WordPress con Elementor senza lacrime (o quasi)"</p>
    </div>
</div>