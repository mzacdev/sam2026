const fs=require('fs');
const path='d:/WWW/e-sports/app/pages/results.php';
const s=fs.readFileSync(path,'utf8');
const start=s.indexOf('<script>');
const end=s.indexOf('</script>',start);
if(start===-1||end===-1){console.log('Script block not found'); process.exit(0);} 
const script=s.slice(start+8,end);
const needle='/assets/img/logos/UA/kpt.png';
const idx=script.indexOf(needle);
console.log('needle idx',idx);
if(idx===-1){console.log('needle not found'); process.exit(0);} 
const before=script.slice(Math.max(0,idx-200), Math.min(script.length, idx+needle.length+200));
console.log('CTX:\n', before);
let single=0,double=0;
for(let i=0;i<idx;i++){ if(script[i]==="'") single++; if(script[i]==='"') double++; }
console.log('single quotes before idx:',single%2? 'odd':'even','(',single,')','double quotes:',double%2? 'odd':'even','(',double,')');
