import React from 'react';
import TreasureLogin from "../TreasureLogin";
import PropTypes from 'prop-types';

export default class TreasureConnectApp extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            user: this.props.user
        }
        this.onUserAuthenticated = this.onUserAuthenticated.bind(this);
    }

    async onUserAuthenticated(uri) {
        const response = await fetch(uri);
        const user = await response.json();
        this.setState({
            user: user
        })
    }

    render() {
        return <div className="card-wrapper">
            <TreasureLogin userAuthenticated={this.onUserAuthenticated}/>
            <div className="card card-info">
                <div className="card-body">
                    <div className="user">
                        {this.state.user ? (
                            <div>Authenticated as <strong>{this.state.user.username}</strong>
                                | <a href="/logout" className="underline">Log out</a>
                            </div>
                            ) : (
                            'Not Authenticated'
                        )}
                    </div>
                    <div className="api">
                        Check out the <a href="/api" className="underline">API Docs</a>
                    </div>
                </div>
            </div>
        </div>
    }
}
TreasureConnectApp.propTypes = {
    user: PropTypes.object
}