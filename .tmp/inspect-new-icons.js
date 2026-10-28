const root=figma.currentPage.children.find(n=>n.name==='NEW FLOW / Admin HD / Maintenance / Index');
const nodes=root.findAll(x=>/^lucide:/.test(x.name||''));
return nodes.slice(0,20).map(n=>({id:n.id,name:n.name,type:n.type,fills:'fills' in n?n.fills:null,strokes:'strokes' in n?n.strokes:null,children:n.children?.length||0,desc:n.findAll?.(x=>x.type==='VECTOR').map(v=>({fills:v.fills,strokes:v.strokes}))}));
