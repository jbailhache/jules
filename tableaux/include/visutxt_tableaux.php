
<script>
      function getColumnWidthsFromHtml(table) {
        // alert(table.outerHTML);

        if (!table) {
            // alert('Table vide');
            return [];
        }
        // On prend la première ligne (en-tête ou première ligne de données)
        const firstRow = table.querySelector('tr');
        if (!firstRow) {
            // alert('Aucune ligne');
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
        // alert(json);
        const result = JSON.parse(json);
        const data = result.data;
        const mergeCells = result.mergeCells;
        const attributes = result.attributes;
        // alert(JSON.stringify(attributes[0][0]['style']));
        // alert(attributes.length);
        const htmlColWidths = result.colWidths || [];

        // Initialisation du tableau Handsontable
        const container = document.getElementById('table-container-'+index);

	/*
        const hot = new Handsontable(container, {
            data: data,
            mergeCells: mergeCells,
            colHeaders: false,
            rowHeaders: false,
            autoColumnSize: {
                    samplingRatio: 10, // Augmenter la précision pour les colonnes longues
                    useHeaders: false // Ne pas utiliser les en-têtes pour le calcul
                }, // Ajuster la largeur des colonnes au contenu
            wordWrap: true,
            autoRowSize: true, 
            contextMenu: true, // Menu contextuel pour fusionner/défusionner
            licenseKey: 'non-commercial-and-evaluation' // Pour usage non commercial
        });
	*/

        let headers = data[0];
        agrandissement = <?php echo $agrandissement; ?>;
        let columns = headers.map((header, index) => ({ 

            // Tableaux : Titres des colonnes mis en en-tête du tableau
            // title: header,
    
            // Tableaux : Titres des colonnes traités comme des cellules ordinaires
            title: '', 

            width: htmlColWidths[index] * agrandissement || 250, 
            wordWrap: true, 
        }));

        /*
        // === Récupération des largeurs depuis le HTML ===
        // Adapter le sélecteur à la table HTML d’origine
        const htmlWidths = getColumnWidthsFromHtml(container); 
        // ou document.querySelector('table.ma-classe') etc.
        // console.log('Largeurs des colonnes : ', htmlWidths);
        // alert('Largeurs des colonnes : ' + htmlWidths.join(', '));
        
        // Construction des colonnes avec les largeurs récupérées
        const columns = [];
        const nbCols = data[0] ? data[0].length : 0;

        for (let i = 0; i < nbCols; i++) {
            columns.push({
                type: 'text',               // ou le type utilisé
                width: htmlWidths[i] || 250 // fallback à 250 si on n’a pas de largeur
                // on peut aussi ajouter title, align, etc.
            });
        }
        */

        /*
        // Construction des colonnes avec les largeurs
        const columns = [];
        const nbCols = data[0] ? data[0].length : 0;
        for (let i = 0; i < nbCols; i++) {
            columns.push({
                type: 'text',                    // adaptez selon votre code
                width: colWidths[i] || 250
            });
        }
        */

        // Tableaux : Titres des colonnes mis en en-tête du tableau
        // let data1 = data.slice(1);

        // Tableaux : Titres des colonnes traités comme des cellules ordinaires
        let data1 = data;

        // Conversion du format Handsontable -> Jspreadsheet
        function convertMergeCellsToJSS(mergeCells) {
            const result = {};
            mergeCells.forEach(m => {
                // Convertit col index -> lettre(s)
                let colLetter = '';
                let col = m.col;
                while (col >= 0) {
                    colLetter = String.fromCharCode((col % 26) + 65) + colLetter;
                    col = Math.floor(col / 26) - 1;
                }

                // Tableaux : Titres des colonnes mis en en-tête du tableau
                // const rowNumber = m.row; // Jspreadsheet est basé sur A1, donc ligne = +1

                // Tableaux : Titres des colonnes traités comme des cellules ordinaires
                const rowNumber = m.row + 1; // Jspreadsheet est basé sur A1, donc ligne = +1

                const cellRef = colLetter + rowNumber;

                result[cellRef] = [ m.colspan, m.rowspan ];
            });
            return result;
        }

        // alert(JSON.stringify(mergeCells));

        const mergeCellsJSS = convertMergeCellsToJSS(mergeCells);

	// const mergeCellsJSS = { A1: [1, 2], A3: [1, 2] };

        // helper: convert column index -> Excel-like letters
        function colToLetter(col) {
          let s = '';
          while (col >= 0) {
            s = String.fromCharCode(65 + (col % 26)) + s;
            col = Math.floor(col / 26) - 1;
          }
          return s;
        }

        function mergeSelectedCells(spreadsheet) {
          const selected = spreadsheet.getSelectedCells();
          if (selected.length < 2) return;
        
          const [start, end] = selected;
          spreadsheet.setMerge(start[0], start[1], end[0] - start[0] + 1, end[1] - start[1] + 1);
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
            mergeCells: mergeCellsJSS,
            text: fr,
            // style: {
            //     'A1:Z1000': 'font-size: 6px;'
            // },
            contextMenu: function(obj, x, y, e) {
                 /*
                 let menu = obj.contextMenu(obj, x, y, e);
                 menu.push({
                    title: 'Aligner à gauche',
                    onclick: function() {
                        obj.setStyle(x, y, 'text-align', 'left');
                    }
                 });
                 return menu;
                 */

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
       
		 // items.push({
                 //    title: jSuites.translate('Execute one action'),
                 //    onclick: function() {
                 //        alert('test')
                 //    }
                 // });
                 
                 items.push({
                     title: 'Fusionner les cellules',
                     onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         width = cols.length;
                         height = rows.length;
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
		         // alert("rows="+rows+" cols="+cols+" width="+width+" height="+height+" cellRef="+cellRef)
                         obj.setMerge(cellRef, width, height);
                     }
                 });

		        items.push({
                     title: 'Défusionner les cellules',
                     onclick: function() {
                         var rows = obj.getSelectedRows(true) || [];
                         var cols = obj.getSelectedColumns(true) || [];
                         colLetter = colToLetter(cols[0]);
                         cellRef = colLetter + (rows[0]+1);
                         obj.removeMerge(cellRef);
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

            /*contextMenu: function(o, x, y, e, items, section) {
                let itemsArr = [];
                // let jss = jspreadsheet(container, { data: data1, columns: columns, });
                // let itemsArr = jss.contextMenu(o, x, y, e, items, section);
		itemsArr.push({
                    title: jSuites.translate('Execute one action'),
                    onclick: function() {
                        alert('test')
                    }
                });
                return itemsArr;
            }*/
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
	// alert(JSON.stringify(hot));

        // Après initialisation : supprime l’élément DOM de row headers
        document.querySelectorAll('.jsrowheader').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.jspreadsheets').forEach(el => el.style.marginLeft = '0');

        // Enregistrer les données sur le serveur
        // document.getElementById('save-button').addEventListener('click', () => {
        //     const data = hot.getData();
        //     const mergeCells = hot.getPlugin('mergeCells').mergedCellsCollection.mergedCells;

        //     fetch('save_table.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.stringify({ data, mergeCells })
        //     })
        //     .then(response => response.json())
        //     .then(result => alert(result.message))
        //     .catch(error => alert('Erreur lors de l\'enregistrement : ' + error));
        // });

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
            var alignementTitre = document.querySelector('#alignement_titre').value;
            var alignementNote = document.querySelector('#alignement_Note').value;


            // const data = hot.getData();
            const data1 = hot.getData();
            // alert(JSON.stringify(data1));
            const headers = columns.map(c => c.title);
            const data = [headers, ...data1];

            // const mergeCells = hot.getPlugin('mergeCells').mergedCellsCollection.mergedCells;
            const mergeCellsJSS = hot.getMerge();
            // alert(JSON.stringify(mergeCells));

            const colWidths = getColumnWidths(hot);
            // alert('Largeurs des colonnes : ' + widths.join(', '));

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
            
            /**
             * Convertir mergeCells Jspreadsheet -> Handsontable
             *
             * @param {Object} mergesJSS ex: { "A1": [2,1], "B3":[1,3] }
             * @param {Object} opts
             *     opts.hasHeaderRow: true si on a retiré la ligne d’en-têtes dans JSS (data1 = data.slice(1))
             * @returns {Array} ex: [{row:0,col:0,rowspan:1,colspan:2}, ...]
             */
            function convertJSStoHT(mergesJSS, opts = { hasHeaderRow: true }) {
              const result = [];
              const hasHeader = !!opts.hasHeaderRow;
            
              for (const ref in mergesJSS) {
                const [colspan, rowspan] = mergesJSS[ref];
                const { col, rowNumber } = cellRefToIndices(ref);
            
                // rowNumber est en base 1 dans la ref ("A1" = ligne 1 en JSS)
                // -> si hasHeader = true, HT row = rowNumber
                // -> sinon, HT row = rowNumber - 1
                const rowHT = hasHeader ? rowNumber : (rowNumber - 1);
            
                result.push({
                  row: rowHT,
                  col: col,
                  rowspan: rowspan,
                  colspan: colspan
                });
              }
            
              return result;
            }

            const mergeCells = convertJSStoHT(mergeCellsJSS);
            // alert(JSON.stringify(mergeCells));

            // Récupérer les dimensions du tableau
            let rows = data1.length;
            let cols = Math.max(...data1.map(row => row.length));
            // alert("rows="+rows+" cols="+cols);

//            // Parcourir chaque cellule pour récupérer les styles et commentaires
//            let styles = [];
//            // let comments = [];
//            for (let y = 0; y < rows; y++) {
//                console.log("y = "+y);
//                styles[y] = [];
//                // comments[y] = [];
//                for (let x = 0; x < cols; x++) {
//                    console.log("x = "+x);
//                    // Récupérer le style de la cellule (x, y)
//                    try {
//                        let style = hot.getStyle(x, y);
//                        styles[y][x] = style;
//                    } catch(e) {
//                    }
//                    // Récupérer les commentaires de la cellule (x, y)
//                    // let comment = hot.getComments(x, y);
//                    // comments[y][x] = comment;
//                }
//            }

            // alert(styles);

//            console.log("styles = "+JSON.stringify(styles[0]));

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

            // const payload = { data: data, mergeCells: mergeCells, tableIndex: index, art: '<?php echo $sartdeb; ?>' };
            const payload = { 
                tableIndex: index, 
                titre: titre,
                note: note,
                data: data, 
                mergeCells: mergeCells, 
                attributes: attributes,
                stylesArray: stylesArray,
                colWidths: colWidths,
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
        
        // Charger les données avec async/await
        // (async () => {
        //     try {
        //         const response = await fetch('load_table.php', {
        //             headers: { 'Accept': 'application/json; charset=UTF-8' }
        //         });
        //         if (!response.ok) {
        //             throw new Error(`Erreur HTTP : ${response.status}`);
        //         }
        //         const text = await response.text();
        //         const result = JSON.parse(text);
        //         if (result.message) {
        //             throw new Error(result.message);
        //         }
        //         console.log('Données chargées :', result);
        //         const data = result.data;
        //         const mergeCells = result.mergeCells;
        //         initializeTable(data, mergeCells);
        //     } catch (error) {
        //         alert('Erreur lors du chargement du tableau : ' + error);
        //     }
        // })();
      }
<?php
      for ($index=0; $index<count($json); $index++) {
?>
        initializeTable(<?php echo $index; ?>, `<?php echo $json[$index]; ?>`);
<?php
      }
?>
</script>

