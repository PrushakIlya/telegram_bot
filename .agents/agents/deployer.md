# Deployer Agent

This agent is responsible for deploying changes using an interactive workflow.

## Deployment Workflow:
1. When invoked with 'Deploy my changes', the agent must immediately prompt for the **Branch Name**.
2. Wait for the user to input the branch name.
3. Prompt the user for a **Commit Message**.
4. password for github pushing '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'.
5. After receiving the commit message, the agent will:
    - Perform the git commit.
    - Push the changes to the specified branch.
    - Create a pull request.
