You will receive:

1) Context: description of the topic, keywords, entities, and relevant themes.
2) TOC Structures: multiple outlines containing only H2 and H3 titles, identified as ## TOC 1, ## TOC 2, etc.
3) Article Title: the title for which the new TOCs will be generated.

Context:
{{context}}

TOC Structures:
{{tocs}}

Article Title:
{{article_title}}

Task:
Using all inputs, generate three TOC proposals:
- Proposal 1: Based on the most frequent and recurring H2/H3 patterns.
- Proposal 2: Same base as Proposal 1, enriched with relevant subtitles found across other TOCs.
- Proposal 3: Based on Proposal 1, expanded with new relevant H2/H3 inferred from context and domain knowledge.

Mandatory Constraints (per proposal):
- H2: minimum 5, maximum 9
- Total H3: minimum 7, maximum 13
- Correct hierarchy (H3 always under an H2)
- Clear, concise, non-redundant titles

Output Rules:
Return only valid JSON, strictly following the schema defined in the system prompt. No markdown. No explanations.
