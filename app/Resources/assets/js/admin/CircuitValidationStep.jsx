import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { StepItem } from '../_components/AdminUI'
import SearchUser from '../_components/SearchUser'

class CircuitValidationStep extends Component {

    static contextTypes = {
        t: func
    }

    static defaultProps = {
        step : {
            user_packs: [],
            users: []
        }
    }

    state = {
        inputDisplayed: false,
        step: {
            users: [],
            user_packs: []
        }
    }

    componentWillReceiveProps(nextProps){
        this.setState({step: nextProps.step})
    }

    handleClickDeleteUser = (stepId, userId) => {
        this.setState(prevState => {
            console.log(prevState)
            prevState.step.users.splice(userId, 1)
            return {
                step: prevState.step
            }
        })
        this.props.handleClickDeleteUser(stepId, userId)
    }

    render() {
        const { t } = this.context
        const { id, step, collectiviteId, handleClickDeleteUser } = this.props
        const listUsers = step.users.map((user, key) => {
                console.log(user)
                return <li key={key}>{user._prenom + " " + user._nom}<a onClick={e => handleClickDeleteUser(id, key)}>x</a></li>
            })
        return (
            
            <StepItem   className="medium-3 cell"
                        title={step.ordre == 0 ? t('admin.circuit.applicant_step', {ordre:step.ordre + 1}) : t('admin.circuit.validat_step', {ordre:step.ordre + 1})}>
                <ul className="no-bullet">
                    {listUsers}
                    {this.state.inputDisplayed ?
                        <SearchUser collectiviteId={collectiviteId}/> :
                        <li><button className="btn-add" type={"button"} onClick={() => this.setState({inputDisplayed: true})}>{t('common.button.add_user')}</button></li>
                    }
                </ul>
            </StepItem>
        )
    }
}

export default translate(['sesile'])(CircuitValidationStep)