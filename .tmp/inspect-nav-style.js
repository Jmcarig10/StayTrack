const ids=['283:1913','282:1520','282:1399','282:1636','292:586'];
const col=f=>f&&f.type==='SOLID'&&f.color?`#${[f.color.r,f.color.g,f.color.b].map(v=>Math.round(v*255).toString(16).padStart(2,'0')).join('')}`:f&&f.type;
const out=[];for(const id of ids){const n=await figma.getNodeByIdAsync(id);out.push({id,name:n&&n.name,fill:n&&n.fills&&n.fills[0]?col(n.fills[0]):null,children:n&&n.children?n.children.map(c=>({id:c.id,name:c.name,type:c.type,fill:c.fills&&c.fills[0]?col(c.fills[0]):null,stroke:c.strokes&&c.strokes[0]?col(c.strokes[0]):null})):[]});}return out;
