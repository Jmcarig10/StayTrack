const ids = ['278:3877','278:3884','278:3938','278:3943','278:3982','278:3987','278:3462','277:1111'];
const out = [];
for (const id of ids) {
  const n = await figma.getNodeByIdAsync(id);
  out.push({id, name:n && n.name, reactions:n && n.reactions ? n.reactions.length : 0, destination:n && n.reactions && n.reactions[0] && n.reactions[0].actions && n.reactions[0].actions[0] ? n.reactions[0].actions[0].destinationId : null});
}
return out;
