const ids=['283:1899','282:1262','282:1371','282:1486','282:1596','278:3503','278:3761'];
const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o;};
const color=f=>{if(!f)return null;if(f.type==='SOLID')return f.color?`#${[f.color.r,f.color.g,f.color.b].map(v=>Math.round(v*255).toString(16).padStart(2,'0')).join('')}`:null;return f.type;};
const out=[];
for(const id of ids){const r=await figma.getNodeByIdAsync(id);const texts=r?all(r).filter(n=>n.type==='TEXT'&&['My dashboard','My apartment','My lease','My bills','Payment history','Maintenance','Profile'].includes(n.characters)):[];out.push({id,name:r&&r.name,items:texts.map(t=>({text:t.characters,parent:t.parent&&t.parent.id,parentName:t.parent&&t.parent.name,fill:t.parent&&t.parent.fills&&t.parent.fills[0]?color(t.parent.fills[0]):null,visible:t.parent&&t.parent.visible,reactions:(t.parent&&t.parent.reactions||[]).length}))});}
return out;
