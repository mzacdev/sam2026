const fs = require('fs');
const path = 'd:/WWW/e-sports/app/pages/results.php';
const s = fs.readFileSync(path,'utf8');
const start = s.indexOf('<script>');
const end = s.indexOf('</script>', start);
if (start === -1 || end === -1) { console.error('script not found'); process.exit(1); }
const script = s.slice(start+8, end);
const needle = '/assets';
let issues = [];
let state = 'normal';
for (let i=0;i<script.length;i++){
    const c = script[i];
    const two = script.substr(i,2);
    if(state==='normal'){
        if(two==='//'){
            // skip to eol
            const nl = script.indexOf('\n', i+2);
            if(nl===-1) break; i = nl; continue;
        }
        if(two==='/*'){
            const endc = script.indexOf('*/', i+2);
            if(endc===-1) break; i = endc+1; continue;
        }
        if(c === "'") { state='single'; continue; }
        if(c === '"') { state='double'; continue; }
        if(c === '`') { state='backtick'; continue; }
        if(script.startsWith(needle, i)){
            issues.push({pos:i, ctx: script.slice(Math.max(0,i-80), Math.min(script.length,i+80))});
            i += needle.length-1;
            continue;
        }
    } else if(state==='single'){
        if(c==='\\') { i++; }
        else if(c==="'") { state='normal'; }
    } else if(state==='double'){
        if(c==='\\') { i++; }
        else if(c==='"') { state='normal'; }
    } else if(state==='backtick'){
        if(c==='\\') { i++; }
        else if(c==='`') { state='normal'; }
    }
}
if(issues.length===0) console.log('No /assets found outside strings');
else{
    console.log('Found', issues.length, '/assets outside strings. Showing contexts:');
    issues.forEach(it=>{
        const lineno = script.slice(0,it.pos).split('\n').length + (start+8>0? (s.slice(0,start+8).split('\n').length-1) : 0);
        console.log('--- pos', it.pos, 'approx line', lineno);
        console.log(it.ctx.replace(/\n/g,'\\n'));
    });
}
