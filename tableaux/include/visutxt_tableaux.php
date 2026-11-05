
<script>
      function getColumnWidthsFromHtml(table) {

        if (!table) {
            return [];
        }
        // On prend la première ligne (en-tête ou première ligne de données)
        const firstRow = table.querySelector('tr');
        if (!firstRow) {
            return [];
        }

        const cells = firstRow.querySelectorAll('th, td');
        const widths = [];

        cells.forEach(cell => {
            // offsetWidth donne la largeur réellement affichée (en pixels)
            // Vous pouvez aussi utiliser getBoundingClientRect().width
            widths.push(Math.round(cell.offsetWidth));
            });

        return widths;
      }

      function initializeTable(index, raw_json) {
        // const raw_json = `<?php echo $json[0]; ?>`;
        const json = raw_json.replace(/\r/g, "\\r");
        const result = JSON.parse(json);
        const data = result.data;
        const attributes = result.attributes;
        const htmlColWidths = result.colWidths || [];
        const initMergeCells = result.mergeCells || {};

        // Initialisation du tableau Handsontable
        const container = document.getElementById('table-container-'+index);

        let headers = data[0];
        agrandissement = <?php echo $agrandissement; ?>;
        let columns = headers.map((header, index) => ({

            // Tableaux : Titres des colonnes mis en en-tête du tableau
            // title: header,

            // Tableaux : Titres des colonnes traités comme des cellules ordinaires
            title: '',

            width: htmlColWidths[index] * agrandissement || 250,
            wordWrag: true,
        }));

        // Tableaux : Titres des colonnes traités comme des cellules ordinaires
        let data1 = data;


        // helper: convert column index -> Excel-like letters
        function colToLetter(col) {
          let s = '';
          while (col >= 0) {
            s = String.fromCharCode(65 + (col % 26)) + s;
            col = Math.floor(col / 26) - 1;
          }
          return s;
        }

        // Fonction pour récupérer les largeurs des colonnes
        function getColumnWidths(table) {
            const columnWidths = [];
            const columns = table.options.columns;

            // Parcourir chaque colonne pour récupérer sa largeur
            columns.forEach((column, index) => {
                // Si la largeur a été modifiée par l'utilisateur, elle sera stockée dans l'objet column
                // Sinon, on utilise la largeur par défaut
                const width = column.width || table.colWidths[index];
                columnWidths.push(width);
            });

            console.log('Largeurs des colonnes :', columnWidths);
            return columnWidths;
        }

        fetch('node_modules/jspreadsheet-ce/resources/lang/fr_FR.json')
        .then(response => response.json())
        .then(fr => {
        // jspreadsheet.setLocale(fr);

        const hot = jspreadsheet(container, {
            data: data1,
            columns: columns,
            text: fr,
            mergeCells: initMergeCells,
            // style: {
            //     'A1:Z1000': 'font-size: 6px;'
            // },
            contextMenu: function(obj, x, y, e) {

                 var items = [];

                 if (y == null) {
                     // Insert a new column
                     if (obj.options.allowInsertColumn == true) {
                         items.push({
                             title:obj.options.text.insertANewColumnBefore,
                             onclick:function() {
                                 obj.insertColumn(1, parseInt(x), 1);
                             }
                         });
                     }

                     if (obj.options.allowInsertColumn == true) {
                         items.push({
                             title:obj.options.text.insertANewColumnAfter,
                             onclick:function() {
                                 obj.insertColumn(1, parseInt(x), 0);
                             }
                         });
                     }

                     // Delete a column
                     if (obj.options.allowDeleteColumn == true) {
                         items.push({
                             title:obj.options.text.deleteSelectedColumns,
                             onclick:function() {
                                 obj.deleteColumn(obj.getSelectedColumns().length ? undefined : parseInt(x));
                             }
                         });
                     }

                     // Rename column
                     if (obj.options.allowRenameColumn == true) {
                         items.push({
                             title:obj.options.text.renameThisColumn,
                             onclick:function() {
                                 obj.setHeader(x);
                             }
                         });
                     }

                     // Sorting
                     if (obj.options.columnSorting == true) {
                         // Line
                         items.push({ type:'line' });

                         items.push({
                             title:obj.options.text.orderAscending,
                             onclick:function() {
                                 obj.orderBy(x, 0);
                             }
                         });
                         items.push({
                             title:obj.options.text.orderDescending,
                             onclick:function() {
                                 obj.orderBy(x, 1);
                             }
                         });
                     }
                 } else {
                     // Insert new row
                     if (obj.options.allowInsertRow == true) {
                         items.push({
                             title:obj.options.text.insertANewRowBefore,
                             onclick:function() {
                                 obj.insertRow(1, parseInt(y), 1);
                             }
                         });

                         items.push({
                             title:obj.options.text.insertANewRowAfter,
                             onclick:function() {
                                 obj.insertRow(1, parseInt(y));
                             }
                         });
                     }

                     if (obj.options.allowDeleteRow == true) {
                         items.push({
                             title:obj.options.text.deleteSelectedRows,
                             onclick:function() {
                                 obj.deleteRow(obj.getSelectedRows().length ? undefined : parseInt(y));
                             }
                         });
                     }

                     if (x) {
                         if (obj.options.allowComments == true) {
                             items.push({ type:'line' });

                             var title = obj.records[y][x].getAttribute('title') || '';

                             items.push({
                                 title: title ? obj.options.text.editComments : obj.options.text.addComments,
                                 onclick:function() {
                                     obj.setComments([ x, y ], prompt(obj.options.text.comments, title));
                                 }
                             });

                             if (title) {
                                 items.push({
                                     title:obj.options.text.clearComments,
                                     onclick:function() {
                                         obj.setComments([ x, y ], '');
                                     }
                                 });
                             }
                         }
                     }
                 }

                 // Line
                 items.push({ type:'line' });

                 // Copy
                 items.push({
                     title: obj.options.text.copy,
                     onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];

                         if (!rows.length || !cols.length) {
                           alert('Aucune sélection détectée.');
                           return;
                         }

                         var r1 = Math.min.apply(null, rows);
                         var r2 = Math.max.apply(null, rows);
                         var c1 = Math.min.apply(null, cols);
                         var c2 = Math.max.apply(null, cols);

                         var data = [];
                         for (var r = r1; r <= r2; r++) {
                           var row = [];
                           for (var c = c1; c <= c2; c++) {
                             var v = obj.getValueFromCoords(c, r); // col, row (v4)
                             row.push(v == null ? '' : v);
                           }
                           data.push(row);
                         }

                         var tsv = data.map(function(r){ return r.join('\t'); }).join('\n');

                         // Copier dans le presse-papiers (Clipboard API + fallback)
                         if (navigator.clipboard && navigator.clipboard.writeText) {
                           navigator.clipboard.writeText(tsv).catch(function(){
                             fallbackCopy(tsv);
                           });
                         } else {
                           fallbackCopy(tsv);
                         }

                         function fallbackCopy(text) {
                           var ta = document.createElement('textarea');
                           ta.value = text;
                           document.body.appendChild(ta);
                           ta.select();
                           document.execCommand('copy');
                           ta.remove();
                         }
                     }

                 });

                 // Paste
                 items.push({
                     title: obj.options.text.paste,
                     onclick: function() {
                         (async function() {
                             try {
                                 var text = await navigator.clipboard.readText();
                             } catch (err) {
                                 alert('Impossible de lire le presse-papiers (permission / HTTPS ?) : ' + (err && err.message ? err.message : err));
                                 return;
                             }

                             var rows = text.split(/\r?\n/).filter(function(r){ return r.length > 0; })
                                 .map(function(r){ return r.split('\t'); });

                             if (!rows.length) return;

                             // Déterminer la cellule de départ : première cellule de la sélection si elle existe,
                             // sinon la cellule cliquée (x,y) fournie au contextMenu.
                             var selRows = obj.getSelectedRows(true) || [];
                             var selCols = obj.getSelectedColumns(true) || [];
                             var startRow = selRows.length ? Math.min.apply(null, selRows) : (y != null ? parseInt(y) : 0);
                             var startCol = selCols.length ? Math.min.apply(null, selCols) : (x != null ? parseInt(x) : 0);

                             for (var r = 0; r < rows.length; r++) {
                                 for (var c = 0; c < rows[r].length; c++) {
                                     var rr = startRow + r;
                                     var cc = startCol + c;
                                     // setValueFromCoords(col, row, value, [force])
                                     // on peut mettre true en 4ème param pour forcer l'écriture sur readonly.
                                     obj.setValueFromCoords(cc, rr, rows[r][c]);
                                 }
                             }
                         })();
                     }

                 });

                 // Save
                 if (obj.options.allowExport) {
                     items.push({
                         title: obj.options.text.saveAs,
                         shortcut: 'Ctrl + S',
                         onclick: function () {
                             obj.download();
                         }
                     });
                 }

                 // About
                 if (obj.options.about) {
                     items.push({
                         title:obj.options.text.about,
                         onclick:function() {
                             alert(obj.options.about);
                         }
                     });
                 }

                 items.push({
                     title: 'Fusionner les cellules',
                     onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];

                         if (!rows.length || !cols.length) {
                             return;
                         }

                         var r1 = Math.min.apply(null, rows);
                         var r2 = Math.max.apply(null, rows);
                         var c1 = Math.min.apply(null, cols);
                         var c2 = Math.max.apply(null, cols);

                         var colspan = c2 - c1 + 1;
                         var rowspan = r2 - r1 + 1;

                         if (colspan < 2 && rowspan < 2) {
                             return;
                         }

                         var cellRef = colToLetter(c1) + (r1 + 1);
                         var topLeftValue = obj.getValueFromCoords(c1, r1);

                         obj.setMerge(cellRef, colspan, rowspan);
                         obj.setValueFromCoords(c1, r1, topLeftValue);
                     }
                 });

                 items.push({
                     title: 'Défusionner les cellules',
                     onclick: function() {
                         var clickedX = (x != null) ? parseInt(x) : null;
                         var clickedY = (y != null) ? parseInt(y) : null;

                         var targetMergeKey = null;

                         if (obj.options.mergeCells) {
                             // Trouver si la cellule cliquée ou une cellule sélectionnée fait partie d'une fusion
                             for (var key in obj.options.mergeCells) {
                                 var mCoords = jspreadsheet.helpers.getCoordsFromColumnName(key);
                                 if (!mCoords) continue;
                                 var mx = mCoords[0];
                                 var my = mCoords[1];
                                 var mColspan = obj.options.mergeCells[key][0];
                                 var mRowspan = obj.options.mergeCells[key][1];

                                 // Test si la cellule cliquée (x, y) est dans cette plage
                                 if (clickedX != null && clickedY != null) {
                                     if (clickedX >= mx && clickedX < mx + mColspan &&
                                         clickedY >= my && clickedY < my + mRowspan) {
                                         targetMergeKey = key;
                                         break;
                                     }
                                 }
                             }

                             // Si pas trouvée via clic direct, vérifier la sélection actuelle
                             if (!targetMergeKey) {
                                 var selRows = obj.getSelectedRows(true) || [];
                                 var selCols = obj.getSelectedColumns(true) || [];
                                 for (var key in obj.options.mergeCells) {
                                     var mCoords = jspreadsheet.helpers.getCoordsFromColumnName(key);
                                     if (!mCoords) continue;
                                     var mx = mCoords[0];
                                     var my = mCoords[1];
                                     var mColspan = obj.options.mergeCells[key][0];
                                     var mRowspan = obj.options.mergeCells[key][1];

                                     for (var r = 0; r < selRows.length; r++) {
                                         for (var c = 0; c < selCols.length; c++) {
                                             if (selCols[c] >= mx && selCols[c] < mx + mColspan &&
                                                 selRows[r] >= my && selRows[r] < my + mRowspan) {
                                                 targetMergeKey = key;
                                                 break;
                                             }
                                         }
                                         if (targetMergeKey) break;
                                     }
                                     if (targetMergeKey) break;
                                 }
                             }
                         }

                         if (targetMergeKey && obj.options.mergeCells[targetMergeKey]) {
                             var mCoords = jspreadsheet.helpers.getCoordsFromColumnName(targetMergeKey);
                             var mx = mCoords[0];
                             var my = mCoords[1];
                             var mColspan = obj.options.mergeCells[targetMergeKey][0];
                             var mRowspan = obj.options.mergeCells[targetMergeKey][1];
                             var mergedValue = obj.getValueFromCoords(mx, my);

                             var emptyData = [];
                             var totalCells = mColspan * mRowspan;
                             for (var i = 0; i < totalCells - 1; i++) {
                                 emptyData.push('');
                             }

                             obj.removeMerge(targetMergeKey, emptyData);
                             obj.setValueFromCoords(mx, my, mergedValue);
                         }
                     }
                 });

                 items.push({
                    title: 'Aligner à gauche',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'text-align', 'left', true);
                    }
                 });

                 items.push({
                    title: 'Aligner à droite',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'text-align', 'right', true);
                    }
                 });

                 items.push({
                    title: 'Aligner au centre',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'text-align', 'center', true);
                    }
                 });

                 items.push({
                    title: 'Justifier',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'text-align', 'justify', true);
                    }
                 });

                 items.push({
                    title: 'Aligner en haut',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'vertical-align', 'top', true);
                    }
                 });

                 items.push({
                    title: 'Aligner en bas',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'vertical-align', 'bottom', true);
                    }
                 });

                 items.push({
                    title: 'Aligner au milieu',
                    onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.setStyle(cellRef, 'vertical-align', 'middle', true);
                    }
                 });

                 return items;
             }

        });

        for (row=0; row<attributes.length; row++) {
            for (col=0; col<attributes[row].length; col++) {

                // Convertit col index -> lettre(s)
                let colLetter = '';
                col1 = col;
                while (col1 >= 0) {
                    colLetter = String.fromCharCode((col1 % 26) + 65) + colLetter;
                    col1 = Math.floor(col1 / 26) - 1;
                }
                // Tableaux : Titres des colonnes mis en en-tête du tableau
                // const rowNumber = m.row; // Jspreadsheet est basé sur A1, donc ligne = +1
                // Tableaux : Titres des colonnes traités comme des cellules ordinaires
                const rowNumber = row + 1; // Jspreadsheet est basé sur A1, donc ligne = +1
                const cellRef = colLetter + rowNumber;

                style = attributes[row][col]['style'];

                console.log("row="+row+" col="+col+" cellRef="+cellRef+" style="+style);

                if (style != null) {
                    arrStyle = style.split(';');
                    console.log(arrStyle);
                    for (const item of arrStyle) {
                        if (item != "") {
                            arrItem = item.split(":");
                            console.log(arrItem);
                            prop = arrItem[0].trim();
                            val = arrItem[1].trim();
                            console.log("prop="+prop+" val="+val);
                            hot.setStyle(cellRef, prop, val, true);
                        }
                    }
                }

            }
        }

        // hot.hideIndex();

        // Après initialisation : supprime l’élément DOM de row headers
        document.querySelectorAll('.jsrowheader').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.jspreadsheets').forEach(el => el.style.marginLeft = '0');

        // Empêcher la modification de largeur d'autres colonnes lors du redimensionnement d'une seule colonne
        if (hot && hot.table) {
            const thead = hot.table.querySelector('thead');
            if (thead) {
                thead.addEventListener('mousedown', function(e) {
                    if (e.target && e.target.getAttribute('data-x') !== null) {
                        const rect = e.target.getBoundingClientRect();
                        if (rect.width - e.offsetX < 6) {
                            hot.headers.forEach(h => h.classList.remove('selected'));
                        }
                    }
                }, true);
            }
        }

        // Enregistrer les données sur le serveur

        document.getElementById('save-button-'+index).addEventListener('click', save_tables);

<?php
    if ($majauto) {
?>
        save_tables();
<?php
    }
?>

        function save_tables() {

            var titre = document.querySelector('.textecourant #titre').value;
            var note = document.querySelector('.textecourant #note').value;
            var alignementTitre = document.querySelector('#alignement_titre') ? document.querySelector('#alignement_titre').value : '';
            var alignementNote = (document.querySelector('#alignement_note') || document.querySelector('#alignement_Note')) ? (document.querySelector('#alignement_note') || document.querySelector('#alignement_Note')).value : '';


            // const data = hot.getData();
            const data1 = hot.getData();
            const headers = columns.map(c => c.title);
            const data = [headers, ...data1];

            const colWidths = getColumnWidths(hot);

            // Convertir une référence de cellule type "B3" en indices (0-based)
            function cellRefToIndices(ref) {
              const match = ref.match(/^([A-Z]+)([0-9]+)$/);
              if (!match) return null;

              let colLetters = match[1];
              let rowNumber = parseInt(match[2], 10);

              // Conversion lettres -> index colonne
              let col = 0;
              for (let i = 0; i < colLetters.length; i++) {
                col = col * 26 + (colLetters.charCodeAt(i) - 64);
              }
              col -= 1; // car A=0
              console.log("ref="+ref+" col="+col+" rowNumber="+rowNumber);
              return { col, rowNumber };
            }


            // Récupérer les dimensions du tableau
            let rows = data1.length;
            let cols = Math.max(...data1.map(row => row.length));

            let styles = hot.getStyle();
            console.log("styles = "+JSON.stringify(styles));
            let stylesArray = [];
            for (const ref in styles) {
                style = styles[ref];
                const { col, rowNumber } = cellRefToIndices(ref);
                console.log("ref:"+ref+" col:"+col+" row:"+rowNumber+" style:"+JSON.stringify(style));
                stylesArray.push({
                  row: rowNumber,
                  col: col,
                  style: style
                });
            }

            const mergeCells = hot.getMerge() || {};

            const payload = {
                tableIndex: index,
                titre: titre,
                note: note,
                data: data,
                attributes: attributes,
                stylesArray: stylesArray,
                colWidths: colWidths,
                mergeCells: mergeCells,
                agrandissement: <?php echo $agrandissement; ?>,
                annee: <?php echo $sannee; ?>,
                art: '<?php echo $sartdeb; ?>',
                doc: '<?php echo $sdocdeb; ?>',
                alignementTitre: alignementTitre,
                alignementNote: alignementNote,
            };

            console.log('Données envoyées :', JSON.stringify(payload));

            fetch('save_table.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json; charset=UTF-8' },
                body: JSON.stringify(payload)
            })
            .then(response => {
                console.log('Réponse brute du serveur :', response);
                if (!response.ok) {
                    throw new Error(`Erreur HTTP : ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Contenu de la réponse :', text);
                try {
                    const result = JSON.parse(text);
<?php
    if (!$majauto) {
?>
                    alert(result.message);
<?php
    }
?>
                } catch (e) {
                    throw new Error('Erreur JSON : ' + e.message + '\nContenu : ' + text);
                }
            })
            .catch(error => alert('Erreur lors de l\'enregistrement : ' + error));
        }

        }); // fetch('node_modules/jspreadsheet-ce/resources/lang/fr_FR.json')

      }
<?php
      for ($index=0; $index<count($json); $index++) {
?>
        initializeTable(<?php echo $index; ?>, `<?php echo $json[$index]; ?>`);
<?php
      }
?>
</script>
