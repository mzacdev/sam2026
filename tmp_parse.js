const fs=require('fs');
const s=fs.readFileSync('d:/WWW/e-sports/app/pages/results.php','utf8');
const start=s.indexOf('<script>');
const end=s.indexOf('</script>',start);
if(start===-1||end===-1){ console.error('script block not found'); process.exit(1); }
const script=s.slice(start+8,end);
try{
    new Function(script);
    console.log('Parsed OK');
}catch(e){
    console.error('Parse error:', e && e.message);
    console.error(e.stack);
    // print a few lines around where error likely is: try to find '/assets' occurrences outside strings
    const needle = '/assets';
    let idx = script.indexOf(needle);
    while(idx!==-1){
        const l = script.slice(Math.max(0, idx-120), Math.min(script.length, idx+120));
        console.error('Context around /assets at', idx, ':\n', l, '\n----');
        idx = script.indexOf(needle, idx+1);
    }
}
