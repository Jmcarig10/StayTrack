const ids = ['277:1063','277:1070','277:1076','277:1082','277:1086','277:1092','277:1098','278:3443','278:3452','277:1271','277:1282','278:1415','278:1525'];
const out = [];
for (const id of ids) { const n = await figma.getNodeByIdAsync(id); out.push({id, name:n && n.name, reactions:n && n.reactions ? n.reactions.length : 0, destination:n && n.reactions && n.reactions[0] && n.reactions[0].actions && n.reactions[0].actions[0] ? n.reactions[0].actions[0].destinationId : null}); }
return out;
