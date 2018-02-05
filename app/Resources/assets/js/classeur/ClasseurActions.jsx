import React, { Component } from 'react'
import { func, array, number, string } from 'prop-types'
import {Button, Textarea} from '../_components/Form'
import { translate } from 'react-i18next'

class ClasseurActions extends Component {
    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
    }

    render() {

        const { actions, action, addComment, submitComment } = this.props
        const { t } = this.context

        return (
            <div className="cell medium-12">
                <div className="grid-x grid-margin-x">
                    <h3 className="cell medium-12">{ t('common.classeurs.comments.name') }</h3>
                </div>

                <div className="grid-x grid-margin-x align-top text-center">
                    <div className="cell medium-2 fa fa-comment"></div>
                    <Textarea
                        id="new-action"
                        name="newAction"
                        placeholder={ t('common.classeurs.comments.new') }
                        onChange={addComment}
                        className="cell medium-10"
                        value={action}
                    />
                </div>

                <div className="grid-x grid-margin-x align-top text-center">
                    <div className="cell medium-2"></div>
                    <Button labelText={ t('common.classeurs.comments.submit') }
                            className="cell medium-10 text-right"
                            onClick={submitComment}
                    />
                </div>

                { actions && actions.map((action) =>
                    <div className="grid-x grid-margin-x align-top text-center" key={action.id}>
                        <div className="cell medium-2 fa fa-comment"></div>
                        <div className="cell medium-10 text-left">
                            <p>
                                { action.action }
                                <span className="text-author"> par { action.user_action._prenom } { action.user_action._nom }</span>
                            </p>
                        </div>
                    </div>
                )}
            </div>

        )
    }
}

ClasseurActions.propsType = {
    actions: array,
    action: string,
    classeur: number,
    addComment: func,
    submitComment: func
}

export default translate(['sesile'])(ClasseurActions)