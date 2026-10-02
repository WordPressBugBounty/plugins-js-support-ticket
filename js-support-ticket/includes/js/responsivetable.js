jQuery(document).ready(function(){
// responsive tables
  
/* `.jsst-table` is the class every CRUD screen written since 5.0 uses, and it
   was the one table this was never told about - so the newest screens were the
   only ones with no way to stack, and the only ones that had to be read
   sideways on a phone. (Roadmap 6.5-UX-RESP)

   Headers are read from `thead th` rather than from every `th` in the table:
   these tables use `<th scope="row">` for the first cell of each row, and
   collecting those too shifted every label by one from the second row on - so
   a stacked row read "Question: Needed". A table with no `thead` is left
   alone; there are no column names to label the cells with, and a stack of
   ": value" lines is worse than the row it replaced. */
jQuery('table#js-support-ticket-table, table#jsst-import-data-result-table, table.js-hlpdsk-modern-table, table.jsst-table, table.jsst-status-table').each(function(i){
  var headertext = [];
  headers = jQuery(this).find('thead th');
  if (!headers.length) { headers = jQuery(this).find('th'); }
  tablebody = jQuery(this).find('tbody tr');
  if (!headers.length || !tablebody.length) { return; }

  for (var i = 0; i < headers.length; i++) {
      var current = headers[i];
      headertext.push(current.textContent.replace(/\r?\n|\r/, "").trim());
  }

  for (var i = 0; row = tablebody[i]; i++) {
    /* `th` as well as `td`: the row-header cell is the first column and needs
       its label like any other once the row is a block. */
    var cols = jQuery(row).find('td, th');
      for (var j = 0; col = cols[j]; j++) {
          if (headertext[j]) { col.setAttribute("data-th", headertext[j]); }
      }
  }
  this.setAttribute('data-jsst-stackable', '1');
  /* The scroller around it is told too, rather than the stylesheet asking
     `:has()` - which is newer than some of the browsers these desks are
     administered from, and would silently leave the scroller in place on
     exactly those. */
  jQuery(this).closest('.jsst-table-wrap').addClass('jsst-wrap-stackable');
  jQuery(this).closest('.jsst-card').addClass('jsst-card-stackable');
});
})
