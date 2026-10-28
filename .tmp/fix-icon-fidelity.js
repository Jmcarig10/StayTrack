const sourceIds = {
  'lucide:house': '277:1166',
  'lucide:layout-dashboard': '277:1176',
  'lucide:building-2': '277:1184',
  'lucide:users': '277:1191',
  'lucide:file-text': '277:1197',
  'lucide:wrench': '277:1203',
  'lucide:receipt': '277:1207',
  'lucide:credit-card': '277:1213',
  'lucide:user-round': '277:1219',
  'lucide:log-out': '277:1232',
  'lucide:bell': '277:1242',
  'lucide:search': '277:1253',
  'lucide:ellipsis': '277:1277'
};
const frames = ['277:1041','277:1304','277:1415','278:1525','278:1636','278:1745','278:1825','278:1905','278:1985','278:2083','278:2163','278:2243','278:2323','278:2421','278:2501','278:2581','278:2661','278:2759','278:2839','278:2919','278:2999','278:3097','278:3177','278:3257','278:3337','278:3420','278:3503','278:3601','278:3681','278:3761'];
const sourceNodes = {};
for (const [name, id] of Object.entries(sourceIds)) sourceNodes[name] = await figma.getNodeByIdAsync(id);
const touched = [];
for (const frameId of frames) {
  const frame = await figma.getNodeByIdAsync(frameId);
  const targets = frame.findAll((n) => n.type === 'RECTANGLE' && !!sourceNodes[n.name]);
  for (const target of targets) {
    const src = sourceNodes[target.name];
    if (!src || target.id === src.id) continue;
    const parent = target.parent;
    const index = parent.children.indexOf(target);
    const replacement = src.clone();
    replacement.name = target.name;
    parent.insertChild(index, replacement);
    target.remove();
    touched.push({ old: target.id, new: replacement.id, name: replacement.name });
  }
}
return { replaced: touched.length, touched };
