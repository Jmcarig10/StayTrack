const ids = ['278:3503','278:3963','278:3574'];
const walk = (n, depth = 0, out = []) => {
  out.push({ id:n.id, name:n.name, type:n.type, x:Math.round(n.x), y:Math.round(n.y), w:Math.round(n.width), h:Math.round(n.height), chars:n.type==='TEXT'?n.characters.slice(0,100):undefined });
  if (depth < 6 && n.children) for (const c of n.children) walk(c, depth+1, out);
  return out;
};
const out=[]; for(const id of ids){const n=await figma.getNodeByIdAsync(id); out.push({id,tree:n?walk(n):null});} return out;
