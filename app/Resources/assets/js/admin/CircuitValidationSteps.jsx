import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { StepItem } from '../_components/AdminUI'
import SearchUser from '../_components/SearchUser'

class CircuitValidationSteps extends Component {

    constructor(props) {
        super(props)
    }

    static contextTypes = {
        t: func
    }

    static defaultProps = {
        step: {
            user_packs: [],
            users: []
        }
    }

    state = {
        inputDisplayed: false,
        steps: []
    }

    componentWillReceiveProps(nextProps) {
        this.setState({})
    }
    

    handleClickDeleteUser = (stepId, userId) => {
        console.log("delete user")
        const { circuit } = this.state
        circuit.etape_groupes[stepId].users.splice(userId, 1)
        this.setState({circuit})
    }

    handleClickDeleteGroup = (stepId, groupId) => {
        const { circuit } = this.state
        circuit = circuit.etape_groupes[stepId].user_packs.splice(groupId, 1)
        this.setState(circuit)
    }

    render() {
        const { t } = this.context
        const { steps, collectiviteId } = this.props
        const users = steps.map((step, key) => <ListStepItem id={key} key={key} collectiviteId={collectiviteId}  /> )
        console.log("step", users)
        const usersGroups = step.user_packs.map(group => <ListUsersGroups group={group} handleClickDeleteGroup={handleClickDeleteGroup} />)
        return (
            <ListStepItem {...this.props} />
        )
    }
}

export default translate(['sesile'])(CircuitValidationSteps)

const ListUsersGroups = ({group, handleClickDeleteGroup}) => {
    return (
        <li>{group.nom}<a onClick={e => handleClickDeleteGroup(key, group.id)}>x</a></li>
    )
}

const ListStepItem = ({ id, collectiviteId, step, handleClickDeleteUser, handleClickDeleteGroup }) => {
    return (
        <StepItem   className="medium-3 cell"
                    title={step.ordre == 0 ? t('admin.circuit.applicant_step', {ordre:step.ordre + 1}) : t('admin.circuit.validat_step', {ordre:step.ordre + 1})}>
            <ul className="no-bullet">
                {users}
                {usersGroups}
                {this.state.inputDisplayed ?
                    <SearchUser collectiviteId={collectiviteId}/> :
                    <li><button className="btn-add" type={"button"} onClick={() => this.setState({inputDisplayed: true})}>{t('common.button.add_user')}</button></li>
                }
            </ul>
        </StepItem>
    )
}