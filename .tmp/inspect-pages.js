return figma.root.children.map(p=>({id:p.id,name:p.name,type:p.type,children:p.children?.length||0,sample:p.children?.slice(0,20).map(n=>({id:n.id,name:n.name,type:n.type}))}));
