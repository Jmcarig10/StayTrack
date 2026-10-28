const n = await figma.getNodeByIdAsync('284:5601');
const all = (x, out = []) => { out.push(x); if (x.children) for (const c of x.children) all(c, out); return out; };
return all(n).filter(x => /^lucide:/.test(x.name || '')).map(x => x.name).filter((v,i,a)=>a.indexOf(v)===i);
