return figma.currentPage.children.filter(n=>n.type==='FRAME'||n.type==='SECTION').map(n=>({id:n.id,type:n.type,name:n.name,parent:n.parent?.name,x:n.x,y:n.y,w:n.width,h:n.height}));
