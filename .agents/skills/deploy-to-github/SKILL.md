---
name: deploy-to-github
description: Automates the process of committing, pushing to GitHub, and creating a pull request. Takes commit message and branch name as input.
---

# Deploy to GitHub Skill

This skill automates the process of deploying changes to GitHub by performing the following steps:
1.  Commits current staged changes with a provided commit message.
2.  Pushes the committed changes to a specified branch on GitHub.
3.  Creates a pull request with the branch name + commit message as the title and body.

## Usage

To use this skill, you can invoke it with the following parameters:

-   `commit_message`: The message for the git commit.
-   `branch_name`: The name of the branch to push to and create the pull request from.

**Example Invocation:**

```
invoke_agent(agent_name="deploy-to-github", prompt="commit_message='Fix: Implemented new feature', branch_name='feature/new-feature'")
```

## Implementation Details

This skill assumes the `gh` (GitHub CLI) tool is installed and authenticated on your system for creating pull requests. If it's not installed, please install it or adjust the skill to use standard git commands for PR creation.

The skill will execute the following shell commands:

1.  `git add .` (to stage all changes)
2.  `git commit -m "<commit_message>"`
3.  `git push origin <branch_name>`
4.  `gh pr create --title "<branch_name> - <commit_message>" --body "<branch_name> - <commit_message>"`

The skill will output messages indicating the success or failure of each step.