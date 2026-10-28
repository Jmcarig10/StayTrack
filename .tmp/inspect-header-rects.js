const root=figma.currentPage.children.find(n=>n.name==='NEW FLOW / Admin HD / Maintenance / Index');
const main=root.children.find(n=>n.name==='Main'); const top=main?.children?.find(n=>n.name==='Topbar');
return {main:main?.children?.map(n=>({id:n.id,name:n.name,type:n.type})), top: top?.findAll(x=>x.type==='FRAME'||x.type==='RECTANGLE'||x.type==='VECTOR').map(x=>({id:x.id,name:x.name,type:x.type,x:x.x,y:x.y,w:x.width,h:x.height,children:x.children?.length||0,fills:'fills' in x?x.fills:null,strokes:'strokes' in x?x.strokes:null})).filter(x=>x.w<80||x.h<80)};
