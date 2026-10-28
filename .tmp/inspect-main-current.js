const n=await figma.getNodeByIdAsync('278:3548'); return n.children?.map(c=>({id:c.id,name:c.name,x:c.x,y:c.y,w:c.width,h:c.height}));
