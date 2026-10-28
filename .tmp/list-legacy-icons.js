const ids = ['179:1511','151:3230','151:3338','179:1731'];
const all = (x, out = []) => { out.push(x); if (x.children) for (const c of x.children) all(c, out); return out; };
const out = [];
for (const id of ids) { const n = await figma.getNodeByIdAsync(id); if (n) out.push(...all(n).map(x=>({id:x.id,name:x.name,type:x.type})).filter(x=>/icon|lucide|check|clock|alert|close|x/i.test(x.name||''))); }
return out;
